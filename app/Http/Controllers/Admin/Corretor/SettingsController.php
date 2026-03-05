<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/corretor/Profile', [
            'profile' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        // implementar atualização de configurações
        return redirect()->route('admin.corretor.settings.index');
    }
}
