<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\GoogleCalendarService;
use App\Models\CalendarSetting;
use Illuminate\Support\Facades\Log;

class CalendarSettingsController extends Controller
{
    protected GoogleCalendarService $gc;

    public function __construct(GoogleCalendarService $gc)
    {
        $this->gc = $gc;
    }

    public function index()
    {
        $settings = CalendarSetting::first();
        $calendars = [];
        $connected = false;
        try {
            $connected = $this->gc->isConnected();
            if ($connected) {
                $calendars = $this->gc->listCalendars();
            }
        } catch (\Throwable $e) {
            Log::warning('CalendarSettingsController::index - failed to fetch calendars: '.$e->getMessage());
        }

        return Inertia::render('admin/calendar/Settings', [
            'settings' => $settings,
            'google' => [
                'connected' => $connected,
                'calendars' => $calendars,
            ],
        ]);
    }

    /**
     * Return settings + calendar list as JSON for AJAX/modal use.
     */
    public function data()
    {
        $settings = CalendarSetting::first();
        $calendars = [];
        $connected = false;
        try {
            $connected = $this->gc->isConnected();
            if ($connected) {
                $calendars = $this->gc->listCalendars();
            }
        } catch (\Throwable $e) {
            Log::warning('CalendarSettingsController::data - failed to fetch calendars: '.$e->getMessage());
        }

        return response()->json([
            'settings' => $settings,
            'google' => [
                'connected' => $connected,
                'calendars' => $calendars,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'calendar_id' => 'nullable|string',
            'timezone' => 'nullable|string',
            'locale' => 'nullable|string',
            'extra' => 'nullable|array',
        ]);

        $settings = CalendarSetting::first();
        if (!$settings) {
            $settings = CalendarSetting::create($data + ['extra' => $data['extra'] ?? null]);
        } else {
            $settings->fill($data);
            $settings->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'settings' => $settings]);
        }

        return redirect()->route('admin.calendar.settings')->with('success', 'Configurações do calendário salvas.');
    }
}
