<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard do módulo corretor de imóveis.
     */
    public function index(): Response
    {
        return Inertia::render('admin/corretor/Dashboard', [
            'modulo' => 'corretor',
        ]);
    }
}
