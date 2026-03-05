<?php
namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected ?Google_Service_Calendar $service = null;
    protected string $calendarId;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setApplicationName(config('app.name'));

        $this->calendarId = env('GOOGLE_CALENDAR_ID', 'primary');

        // Prefer service account JSON if present
        $saPath = storage_path('app/google-service-account.json');
        if (file_exists($saPath)) {
            $client->setAuthConfig($saPath);
            $client->setScopes([Google_Service_Calendar::CALENDAR]);
            $this->service = new Google_Service_Calendar($client);
            return;
        }

        // Fall back to OAuth client credentials (user consent flow)
        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $redirect = env('GOOGLE_REDIRECT_URI');

        if ($clientId && $clientSecret && $redirect) {
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri($redirect);
            $client->setScopes([Google_Service_Calendar::CALENDAR]);

            $tokenPath = storage_path('app/google-oauth-token.json');
            if (file_exists($tokenPath)) {
                $token = json_decode(file_get_contents($tokenPath), true);
                Log::info('GoogleCalendarService: token file found; has_refresh=' . (!empty($token['refresh_token']) ? '1' : '0'));
                // try to ensure we have a fresh access token; if there's a refresh token, use it
                try {
                    if (!empty($token['refresh_token'])) {
                        // prefer fetching a fresh access token using the refresh token
                        $newToken = $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                        $fetched = !empty($newToken['access_token']) && empty($newToken['error']);
                        Log::info('GoogleCalendarService: attempted refresh; fetched_access=' . ($fetched ? '1' : '0') . ', has_error=' . (!empty($newToken['error']) ? '1' : '0'));
                        if ($fetched) {
                            $client->setAccessToken($newToken);
                            // persist the freshly obtained token (only store minimal token structure)
                            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                        } else {
                            // fallback to using stored token if fetch didn't return access_token
                            $client->setAccessToken($token);
                        }
                    } else {
                        $client->setAccessToken($token);
                    }
                } catch (\Throwable $e) {
                    Log::error('GoogleCalendarService: token refresh/fetch failed: '.$e->getMessage());
                    $client->setAccessToken($token);
                }

                // if we now have a valid access token, initialize the service
                $hasAt = $client->getAccessToken() && !empty($client->getAccessToken()['access_token']);
                $expired = $client->isAccessTokenExpired();
                Log::info('GoogleCalendarService: post-token status has_access_token=' . ($hasAt ? '1' : '0') . ', expired=' . ($expired ? '1' : '0'));
                if ($hasAt && !$expired) {
                    $this->service = new Google_Service_Calendar($client);
                    Log::info('GoogleCalendarService: Google service initialized via OAuth token.');
                    return;
                }
            }

            Log::warning('GoogleCalendarService: OAuth credentials present but no valid token found. Please run the auth flow.');
            $this->service = null;
            return;
        }

        Log::warning('GoogleCalendarService: no credentials (service account or OAuth) configured.');
        $this->service = null;
    }

    public function listEvents($timeMin = null)
    {
        if (!$this->service) {
            Log::warning('GoogleCalendarService: listEvents called but service is not configured.');
            return [];
        }

        $opts = [];
        if ($timeMin) {
            $opts['timeMin'] = $timeMin;
        }
        // ensure recurring events are expanded and results are ordered by start
        $opts['singleEvents'] = true;
        $opts['orderBy'] = 'startTime';

        $events = $this->service->events->listEvents($this->calendarId, $opts);
        $items = $events->getItems();
        Log::info('GoogleCalendarService: listEvents retrieved count=' . count($items));
        $mapped = [];
        foreach ($items as $e) {
            $rawStart = $e->getStart()?->getDateTime() ?? $e->getStart()?->getDate();
            $rawEnd = $e->getEnd()?->getDateTime() ?? $e->getEnd()?->getDate();

            // sanitize strings like 'undefined' that sometimes leak from other systems
            $summary = $e->getSummary() ? str_replace('undefined', '', $e->getSummary()) : '';
            $description = $e->getDescription() ? str_replace('undefined', '', $e->getDescription()) : '';

            // normalize empty/invalid dates
            $start = $rawStart;
            $end = $rawEnd;
            if (empty($start) || in_array($start, ['0', '1970-01-01T00:00:00Z', '1969-12-31T21:00:00-03:00'])) {
                Log::warning('GoogleCalendarService: skipping event due to invalid start', ['summary' => substr((string)$summary,0,80), 'start' => $start]);
                // skip events without valid start
                continue;
            }

            // detect obviously invalid epoch-like starts and map them to a sensible all-day date
            $allDay = false;
            $mappedStart = $start;
            if (!empty($mappedStart)) {
                $ts = strtotime($mappedStart);
                if ($ts !== false && $ts > 0 && $ts < 86400) {
                    // epoch-ish value (before 1970-01-02) — treat as all-day and map to today
                    Log::warning('GoogleCalendarService: mapping epoch-like start to today', ['id' => $e->getId(), 'orig' => $mappedStart]);
                    $mappedStart = date('Y-m-d');
                    $allDay = true;
                }
                // if start is a date-only string (YYYY-MM-DD), mark allDay
                if (is_string($mappedStart) && strpos($mappedStart, 'T') === false) {
                    $allDay = true;
                }
            }

            $mapped[] = [
                'id' => $e->getId(),
                'summary' => trim($summary),
                'start' => $mappedStart,
                'end' => $end,
                'description' => trim($description),
                'allDay' => $allDay,
                'calendar' => $this->calendarId,
            ];
        }

        return $mapped;
    }

    /**
     * List available calendars for the authenticated account.
     * Returns array of ['id' => ..., 'summary' => ...]
     */
    public function listCalendars()
    {
        if (!$this->service) {
            Log::warning('GoogleCalendarService: listCalendars called but service is not configured.');
            return [];
        }

        try {
            $calList = $this->service->calendarList->listCalendarList();
            $items = $calList->getItems() ?? [];
            $mapped = [];
            foreach ($items as $c) {
                $mapped[] = [
                    'id' => $c->getId(),
                    'summary' => $c->getSummary(),
                    'accessRole' => $c->getAccessRole(),
                ];
            }
            return $mapped;
        } catch (\Throwable $e) {
            Log::error('GoogleCalendarService: listCalendars failed - ' . $e->getMessage());
            return [];
        }
    }

    public function createEvent(array $data)
    {
        if (!$this->service) {
            Log::error('GoogleCalendarService: createEvent called but service is not configured.');
            throw new \RuntimeException('Google Calendar credentials not configured.');
        }

        // normalize start/end for Google API (handle date-only all-day and missing seconds/timezone)
        $startPayload = $this->normalizeDateForGoogle($data['start'] ?? null, $data['allDay'] ?? false);
        $endPayload = $this->normalizeDateForGoogle($data['end'] ?? null, $data['allDay'] ?? false);

        // Google Calendar expects all-day 'end.date' to be exclusive (day after last day).
        // If caller provided all-day dates where end.date equals start.date (common UX), bump end by one day.
        if (!empty($startPayload) && !empty($endPayload) && isset($startPayload['date']) && isset($endPayload['date'])) {
            try {
                $sd = new \DateTime($startPayload['date']);
                $ed = new \DateTime($endPayload['date']);
                if ($sd->format('Y-m-d') === $ed->format('Y-m-d')) {
                    $ed->modify('+1 day');
                    $endPayload['date'] = $ed->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                Log::warning('GoogleCalendarService: failed to normalize all-day end date: '.$e->getMessage());
            }
        }

        $payload = [
            'summary' => $data['summary'],
            'description' => $data['description'] ?? null,
            'start' => $startPayload,
            'end' => $endPayload ?: $startPayload,
        ];

        Log::info('GoogleCalendarService::createEvent payload', $payload);

        $event = new Google_Service_Calendar_Event($payload);
        $created = $this->service->events->insert($this->calendarId, $event);
        return ['id' => $created->getId(), 'htmlLink' => $created->getHtmlLink()];
    }

    public function deleteEvent(string $eventId)
    {
        if (!$this->service) {
            Log::error('GoogleCalendarService: deleteEvent called but service is not configured.');
            throw new \RuntimeException('Google Calendar credentials not configured.');
        }

        $this->service->events->delete($this->calendarId, $eventId);
    }

    public function updateEvent(string $eventId, array $data)
    {
        if (!$this->service) {
            Log::error('GoogleCalendarService: updateEvent called but service is not configured.');
            throw new \RuntimeException('Google Calendar credentials not configured.');
        }
        $event = new Google_Service_Calendar_Event([
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        // set start/end if provided
        if (!empty($data['start'])) {
            $event->setStart($this->normalizeDateForGoogle($data['start'], $data['allDay'] ?? false));
        }
        if (!empty($data['end'])) {
            $event->setEnd($this->normalizeDateForGoogle($data['end'], $data['allDay'] ?? false));
        }

        $updated = $this->service->events->patch($this->calendarId, $eventId, $event);
        return ['id' => $updated->getId(), 'htmlLink' => $updated->getHtmlLink()];
    }

    /**
     * Normalize a date string for Google API.
     * If $allDay is true or the string is date-only (YYYY-MM-DD) returns ['date' => 'YYYY-MM-DD'].
     * Otherwise returns ['dateTime' => 'RFC3339 string'] ensuring seconds and timezone (Z if absent).
     */
    protected function normalizeDateForGoogle($value, bool $allDay = false)
    {
        if (empty($value)) return null;

        // if already an array (caller may pass), return as-is
        if (is_array($value)) return $value;

        $val = trim((string)$value);

        // date-only (YYYY-MM-DD)
        if ($allDay || preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            // for all-day events Google expects end.date to be the day AFTER the end date for single-day events,
            // but callers often expect inclusive end; keep caller-provided end if present and leave adjustment to caller.
            return ['date' => $val];
        }

        // ensure seconds
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $val)) {
            $val .= ':00';
        }

        // if timezone offset missing, append Z
        if (!preg_match('/[Z\+\-]\d{2}:?\d{2}$|Z$/', $val)) {
            $val .= 'Z';
        }

        return ['dateTime' => $val];
    }

    /**
     * Return true if there is a usable OAuth token (or service account was configured).
     */
    public function isConnected(): bool
    {
        // service account configured
        if ($this->service) return true;

        $tokenPath = storage_path('app/google-oauth-token.json');
        if (!file_exists($tokenPath)) return false;

        $token = json_decode(file_get_contents($tokenPath), true);
        if (!$token || !is_array($token)) return false;

        // If refresh_token available we can reconnect
        if (!empty($token['refresh_token'])) return true;

        // Check expiry: some tokens include 'created' + 'expires_in'
        if (isset($token['created']) && isset($token['expires_in'])) {
            return ($token['created'] + $token['expires_in']) > time();
        }

        // Some flows use expiry_date (ms since epoch)
        if (isset($token['expiry_date'])) {
            $expiry = (int) $token['expiry_date'];
            if ($expiry > 10000000000) {
                // milliseconds
                $expiry = (int)($expiry / 1000);
            }
            return $expiry > time();
        }

        // Fallback: if access_token present assume valid
        return !empty($token['access_token']);
    }
}