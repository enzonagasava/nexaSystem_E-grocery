<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

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

        // if there are no events, return a harmless placeholder so the calendar shows something
        if (empty($events)) {
            $today = date('Y-m-d');
            $events = [[
                'id' => 'placeholder-1',
                'summary' => 'Sem agendamentos — exemplo',
                'start' => $today,
                'end' => null,
                'description' => 'Nenhum agendamento encontrado. Este é um exemplo local.',
                'allDay' => true,
            ]];
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
        ]);
        Log::info('CalendarController::store input', $data);
        try {
            $event = $this->gc->createEvent($data);
            return response()->json($event, 201);
        } catch (\Throwable $e) {
            \Log::error('CalendarController::store - '.$e->getMessage());
            return response()->json(['error' => 'Não foi possível criar o evento.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'summary' => 'required|string',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'description' => 'nullable|string',
        ]);
        try {
            $event = $this->gc->updateEvent($id, $data);
            return response()->json($event);
        } catch (\Throwable $e) {
            \Log::error('CalendarController::update - '.$e->getMessage());
            return response()->json(['error' => 'Não foi possível atualizar o evento.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->gc->deleteEvent($id);
            return response()->json(['deleted' => true]);
        } catch (\Throwable $e) {
            \Log::error('CalendarController::destroy - '.$e->getMessage());
            return response()->json(['error' => 'Não foi possível excluir o evento.'], 500);
        }
    }
}