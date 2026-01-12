<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard do módulo e-commerce.
     */
    public function index(): Response
    {
        return Inertia::render('admin/ecommerce/Dashboard', [
            'modulo' => 'ecommerce',
        ]);
    }
}
