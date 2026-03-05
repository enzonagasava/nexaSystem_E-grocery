<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RelatoriosController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('admin/corretor/Relatorios');
    }
}
