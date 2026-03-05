<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    protected $connection = 'tenant_content';
    protected $fillable = [
        'google_id',
        'summary',
        'description',
        'label',
        'label_color',
        'start',
        'end',
        'all_day',
    ];

    protected $casts = [
        'all_day' => 'boolean',
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * Create an event (best-effort to Google) and always persist local copy.
     * Returns array with keys: 'googleEvent' (mixed|null) and 'localEvent' (CalendarEvent).
     */
    public static function createFromPayload(array $data, bool $allDay = true, ?GoogleCalendarService $gc = null): array
    {
        $googleEvent = null;
        if ($gc) {
            try {
                $googleEvent = $gc->createEvent($data);
            } catch (\Throwable $e) {
                Log::warning('CalendarEvent::createFromPayload - google create failed: '.$e->getMessage());
                $googleEvent = null;
            }
        }

        $localEvent = self::create([
            'google_id' => $googleEvent['id'] ?? ($googleEvent->id ?? null),
            'summary' => $data['summary'],
            'description' => $data['description'] ?? null,
            'label' => $data['label'] ?? null,
            'label_color' => $data['labelColor'] ?? ($data['label_color'] ?? null),
            'start' => $data['start'],
            'end' => $data['end'] ?? null,
            'all_day' => $allDay,
        ]);

        if ($googleEvent) {
            $localEvent->google_id = $googleEvent['id'] ?? ($googleEvent->id ?? $localEvent->google_id);
            $localEvent->save();
        }

        return ['googleEvent' => $googleEvent, 'localEvent' => $localEvent];
    }

    /**
     * Update an event. If $id starts with 'local-' updates local record only.
     * Returns array with keys: 'googleEvent' (mixed|null|'error') and 'localEvent' (CalendarEvent|null).
     */
    public static function updateFromPayload(string $id, array $data, bool $allDay = true, ?GoogleCalendarService $gc = null): array
    {
        if (str_starts_with($id, 'local-')) {
            $localId = substr($id, 6);
            $evModel = self::find($localId);
            if (! $evModel) {
                return ['googleEvent' => 'not_found', 'localEvent' => null];
            }

            $evModel->summary = $data['summary'];
            $evModel->start = $data['start'];
            $evModel->end = $data['end'] ?? null;
            $evModel->description = $data['description'] ?? null;
            $evModel->label = $data['label'] ?? $evModel->label;
            $evModel->label_color = $data['labelColor'] ?? ($data['label_color'] ?? $evModel->label_color);
            $evModel->all_day = $allDay;
            $evModel->save();

            return ['googleEvent' => null, 'localEvent' => $evModel];
        }

        $googleEvent = null;
        if ($gc) {
            try {
                $googleEvent = $gc->updateEvent($id, $data);
            } catch (\Throwable $e) {
                Log::warning('CalendarEvent::updateFromPayload - google update failed: '.$e->getMessage());
                $googleEvent = null;
            }
        }

        $evModel = self::where('google_id', $id)->first();
        if (! $evModel) {
            $evModel = self::create([
                'google_id' => $id,
                'summary' => $data['summary'],
                'description' => $data['description'] ?? null,
                'label' => $data['label'] ?? null,
                'label_color' => $data['labelColor'] ?? ($data['label_color'] ?? null),
                'start' => $data['start'],
                'end' => $data['end'] ?? null,
                'all_day' => $allDay,
            ]);
        } else {
            $evModel->summary = $data['summary'];
            $evModel->start = $data['start'];
            $evModel->end = $data['end'] ?? null;
            $evModel->description = $data['description'] ?? null;
            $evModel->all_day = $allDay;
            $evModel->save();
        }

        return ['googleEvent' => $googleEvent, 'localEvent' => $evModel];
    }

    /**
     * Delete an event by id. Accepts 'local-{id}' or google id.
     * Returns bool success.
     */
    public static function deleteById(string $id, ?GoogleCalendarService $gc = null): bool
    {
        try {
            if (str_starts_with($id, 'local-')) {
                $localId = substr($id, 6);
                $evModel = self::find($localId);
                if ($evModel) {
                    if ($evModel->google_id && $gc) {
                        try {
                            $gc->deleteEvent($evModel->google_id);
                        } catch (\Throwable $e) {
                            Log::warning('CalendarEvent::deleteById - google delete (from local) failed: '.$e->getMessage());
                        }
                    }
                    $evModel->delete();
                }

                $local = Cache::get('local_calendar_events') ?: [];
                $filtered = array_values(array_filter($local, fn($le) => ($le['id'] ?? '') !== $id));
                Cache::put('local_calendar_events', $filtered, now()->addDays(7));

                return true;
            }

            if ($gc) {
                try {
                    $gc->deleteEvent($id);
                } catch (\Throwable $e) {
                    Log::warning('CalendarEvent::deleteById - google delete failed: '.$e->getMessage());
                }
            }

            $evModel = self::where('google_id', $id)->first();
            if ($evModel) {
                $evModel->delete();
            }

            $local = Cache::get('local_calendar_events') ?: [];
            $filtered = array_values(array_filter($local, fn($le) => ($le['id'] ?? '') !== $id));
            Cache::put('local_calendar_events', $filtered, now()->addDays(7));

            return true;
        } catch (\Throwable $ex) {
            Log::error('CalendarEvent::deleteById - failed: '.$ex->getMessage());
            return false;
        }
    }

    /**
     * Return array used by the API/calendar UI for this model.
     */
    public function toCalendarApiFormat(): array
    {
        return [
            'id' => $this->google_id ?? 'local-'.$this->id,
            'summary' => $this->summary,
            'start' => $this->all_day ? Carbon::parse($this->start)->format('Y-m-d') : Carbon::parse($this->start)->format(DATE_ATOM),
            'end' => $this->end ? ($this->all_day ? Carbon::parse($this->end)->format('Y-m-d') : Carbon::parse($this->end)->format(DATE_ATOM)) : null,
            'description' => $this->description,
            'label' => $this->label,
            'labelColor' => $this->label_color,
            'allDay' => (bool) $this->all_day,
        ];
    }

    /**
     * Merge Google's events with local DB and legacy cache entries.
     * Returns an array ready to be returned by the controller JSON response.
     */
    public static function mergeWithGoogle(array $events): array
    {
        try {
            $googleIds = array_map(fn($ev) => $ev['id'] ?? null, $events);

            $localOnly = self::whereNull('google_id')->get()->map(fn($e) => $e->toCalendarApiFormat())->toArray();

            $dbMissingInGoogle = self::whereNotNull('google_id')->get()->filter(function ($e) use ($googleIds) {
                return !in_array($e->google_id, $googleIds);
            })->map(fn($e) => $e->toCalendarApiFormat())->toArray();

            $events = array_merge($events, $dbMissingInGoogle, $localOnly);

            $cached = Cache::get('local_calendar_events') ?: [];
            if (!empty($cached) && is_array($cached)) {
                $existing = array_column($events, 'id');
                foreach ($cached as $c) {
                    if (!in_array($c['id'] ?? null, $existing)) {
                        $events[] = $c;
                        $existing[] = $c['id'] ?? null;
                    }
                }
            }

            // return events as-is (may be empty) — controller/UI will decide what to show
            return $events;
        } catch (\Throwable $e) {
            Log::warning('CalendarEvent::mergeWithGoogle - failed to merge local events: '.$e->getMessage());
            // on error return empty list — controller can handle messaging
            return [];
        }
    }
}
