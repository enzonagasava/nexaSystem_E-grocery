<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Requests\Settings\EmpresaUpdateRequest;
use App\Http\Requests\Settings\RedesSociaisUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;
use App\Models\RedeSocial;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class InfoEmpresaController extends Controller
{
    /**
     * Renderiza a página de configurações da empresa
     */
    public function geral(Request $request): Response
    {
        $empresa = Empresa::firstOrFail();

        return Inertia::render('admin/configuracoes/empresa/Empresa', [
            'empresa' => $empresa
        ]);
    }

    /**
     * Atualizar dados da empresa
     */
    public function updateGeral(EmpresaUpdateRequest $request): RedirectResponse
    {
        $empresa = Empresa::firstOrFail();
        $empresa->update($request->validated());

         return back()->with('success', 'Informações da empresa atualizadas com sucesso!');
    }

    /**
     * Renderiza a página de configurações do logo.
     */
        public function Logo(Request $request): Response
    {
        $empresa = Empresa::firstOrFail();
        $logo = $empresa->logo ? Storage::url($empresa->logo) : null;

        return Inertia::render('admin/configuracoes/empresa/Logo', [
            'logo' => $logo
        ]);
    }

      /**
     * Atualizar dados do logo da empresa
     */
        public function updateLogo(Request $request): RedirectResponse
    {
        if (!$request->filled('logo')) {
              return back()->with('success', 'Logo atualizada com sucesso!');
        }
        $request->validate([
          'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $path = $request->file('logo')->store('images/logo', 'public');

        $empresa = Empresa::firstOrFail();
        $empresa->update([
              'logo' => $path,
        ]);

        return back()->with('success', 'Logo atualizada com sucesso!');
    }

    /**
     * Renderiza a página de configurações das redes sociais
     */
        public function RedesSociais(Request $request): Response
    {
        $rede = RedeSocial::firstOrFail();
        return Inertia::render('admin/configuracoes/empresa/RedesSociais', [
            'rede' => $rede
        ]);
    }

    /**
     * Atualizar dados das redes sociais da empresa
     */
        public function updateRedes(RedesSociaisUpdateRequest $request): RedirectResponse
    {
        $rede = RedeSocial::firstOrFail();
        $rede->update($request->validated());

        return back()->with('success', 'Informações das redes sociais atualizadas com sucesso!');
    }
}
