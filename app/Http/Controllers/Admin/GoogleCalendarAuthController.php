<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Google_Client;
use Illuminate\Support\Facades\Log;

class GoogleCalendarAuthController extends Controller
{
    public function redirect()
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $redirect = env('GOOGLE_REDIRECT_URI');

        if (!$clientId || !$clientSecret || !$redirect) {
            return redirect()->route('admin.calendar.index')->with('error', 'Google OAuth not configured.');
        }

        $client = new Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirect);
        $client->setScopes([\Google_Service_Calendar::CALENDAR]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('admin.calendar.index')->with('error', 'Authorization code not provided.');
        }

        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));

        try {
            $token = $client->fetchAccessTokenWithAuthCode($code);
            $tokenPath = storage_path('app/google-oauth-token.json');
            file_put_contents($tokenPath, json_encode($token));
            return redirect()->route('admin.calendar.index')->with('success', 'Google Calendar connected.');
        } catch (\Throwable $e) {
            Log::error('GoogleCalendarAuthController::callback - '.$e->getMessage());
            return redirect()->route('admin.calendar.index')->with('error', 'Failed to fetch Google token.');
        }
    }

    public function disconnect(Request $request)
    {
        $tokenPath = storage_path('app/google-oauth-token.json');
        try {
            if (file_exists($tokenPath)) {
                @unlink($tokenPath);
            }
            return redirect()->route('admin.calendar.index')->with('success', 'Google disconnected.');
        } catch (\Throwable $e) {
            Log::error('GoogleCalendarAuthController::disconnect - '.$e->getMessage());
            return redirect()->route('admin.calendar.index')->with('error', 'Could not disconnect Google.');
        }
    }
}
