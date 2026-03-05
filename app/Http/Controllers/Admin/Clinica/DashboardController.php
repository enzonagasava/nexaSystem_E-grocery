<?php

namespace App\Http\Controllers\Admin\Clinica;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard do módulo clínica médica.
     */
    public function index(): Response
    {
        return Inertia::render('admin/clinica/Dashboard', [
        ]);
    }
}
