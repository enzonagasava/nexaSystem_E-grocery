<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\CalendarEvent;

class CalendarController extends Controller
{
    protected GoogleCalendarService $gc;

    public function __construct(GoogleCalendarService $gc)
    {
        $this->gc = $gc;
    }

    public function index()
    {
        $oauthConfigured = env('GOOGLE_CLIENT_ID') && env('GOOGLE_CLIENT_SECRET') && env('GOOGLE_REDIRECT_URI');
        $connected = false;
        try {
            $connected = $this->gc->isConnected();
        } catch (\Throwable $e) {
            \Log::warning('CalendarController::index - failed to check Google connection: '.$e->getMessage());
            $connected = false;
        }

        return Inertia::render('admin/calendar/Calendar', [
            'google' => [
                'oauth_configured' => (bool) $oauthConfigured,
                'connected' => $connected,
            ],
        ]);
    }

    public function events(Request $request)
    {
        try {
            $events = $this->gc->listEvents($request->input('timeMin'));
        } catch (\Throwable $e) {
            \Log::error('CalendarController::events - '.$e->getMessage());
            $events = [];
        }

        // delegate merging/formatting to the model (it will include DB and legacy cache)
        try {
            $events = CalendarEvent::mergeWithGoogle($events);
        } catch (\Throwable $e) {
            Log::warning('CalendarController::events - failed to merge local events: '.$e->getMessage());
            $events = [];
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'summary' => 'required|string',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'description' => 'nullable|string',
            'label' => 'nullable|string',
            'labelColor' => 'nullable|string',
        ]);
        Log::info('CalendarController::store input', $data);

        $allDay = $request->input('allDay', true);
        try {
            $result = CalendarEvent::createFromPayload($data, $allDay, $this->gc);

            if (!empty($result['googleEvent'])) {
                return response()->json($result['googleEvent'], 201);
            }

            return response()->json($result['localEvent']->toCalendarApiFormat(), 201);
        } catch (\Throwable $ex) {
            Log::error('CalendarController::store - failed to persist event: '.$ex->getMessage());
            // last resort: cache (legacy)
            try {
                $local = Cache::get('local_calendar_events') ?: [];
                $localEvent = [
                    'id' => 'local-'.uniqid(),
                    'summary' => $data['summary'],
                    'start' => $data['start'],
                    'end' => $data['end'] ?? null,
                    'description' => $data['description'] ?? null,
                    'allDay' => $data['allDay'] ?? true,
                ];
                $local[] = $localEvent;
                Cache::put('local_calendar_events', $local, now()->addDays(7));
                return response()->json($localEvent, 201);
            } catch (\Throwable $e2) {
                Log::error('CalendarController::store - fallback store failed: '.$e2->getMessage());
                return response()->json(['error' => 'Não foi possível criar o evento.'], 500);
            }
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'summary' => 'required|string',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'description' => 'nullable|string',
            'label' => 'nullable|string',
            'labelColor' => 'nullable|string',
        ]);
        // delegate to model which handles google best-effort + local persistence
        try {
            $res = CalendarEvent::updateFromPayload($id, $data, $request->input('allDay', true), $this->gc);

            if (isset($res['googleEvent']) && $res['googleEvent'] === 'not_found') {
                return response()->json(['error' => 'Evento não encontrado.'], 404);
            }

            if (!empty($res['googleEvent'])) {
                return response()->json($res['googleEvent']);
            }

            return response()->json($res['localEvent']->toCalendarApiFormat());
        } catch (\Throwable $ex) {
            Log::error('CalendarController::update - fallback update failed: '.$ex->getMessage());
            return response()->json(['error' => 'Não foi possível atualizar o evento.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ok = CalendarEvent::deleteById($id, $this->gc);
            if ($ok) {
                return response()->json(['deleted' => true]);
            }

            return response()->json(['error' => 'Não foi possível excluir o evento.'], 500);
        } catch (\Throwable $ex) {
            Log::error('CalendarController::destroy - fallback delete failed: '.$ex->getMessage());
            return response()->json(['error' => 'Não foi possível excluir o evento.'], 500);
        }
    }
}