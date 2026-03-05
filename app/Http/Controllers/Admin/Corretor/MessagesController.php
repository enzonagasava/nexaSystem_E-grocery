<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessagesController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/corretor/Messages', [
            'conversations' => [],
        ]);
    }

    public function send(Request $request)
    {
        // implementar envio de mensagem
        return response()->json(['success' => true]);
    }
}
