<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RelatorioController extends Controller
{
    /**
     * Exibe o dashboard de relatórios.
     */
    public function index(): Response
    {
        return Inertia::render('admin/corretor/relatorios/Index', [
            'relatorios' => [],
        ]);
    }

    /**
     * Gera relatório de vendas.
     */
    public function vendas(): Response
    {
        return Inertia::render('admin/corretor/relatorios/Vendas', [
            'dados' => [],
        ]);
    }

    /**
     * Gera relatório de leads.
     */
    public function leads(): Response
    {
        return Inertia::render('admin/corretor/relatorios/Leads', [
            'dados' => [],
        ]);
    }

    /**
     * Gera relatório financeiro.
     */
    public function financeiro(): Response
    {
        return Inertia::render('admin/corretor/relatorios/Financeiro', [
            'dados' => [],
        ]);
    }
}
