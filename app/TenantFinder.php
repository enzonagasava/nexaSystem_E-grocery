<?php

namespace App;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\TenantFinder\TenantFinder as BaseTenantFinder;

class TenantFinder extends BaseTenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();
        $mainDomain = env('MAIN_SUBDOMAIN', 'localhost');
        
        Log::info("🔍 TenantFinder - Host recebido: {$host}");
        Log::info("🔍 TenantFinder - Main domain: {$mainDomain}");
        
        // Se for o domínio principal, não busca tenant
        if ($host === $mainDomain) {
            Log::info("✅ TenantFinder - Domínio principal detectado, sem tenant");
            return null;
        }
        
        // Trata localhost para desenvolvimento
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            $tenant = Tenant::first();
            Log::info("🏠 TenantFinder - Localhost detectado, usando primeiro tenant: " . ($tenant ? $tenant->id : 'nenhum'));
            return $tenant;
        }
        
        // Extrai subdomínio removendo o domínio base
        $subdomain = $this->extractSubdomain($host);
        
        if (!$subdomain) {
            Log::warning("⚠️ TenantFinder - Não foi possível extrair subdomínio de: {$host}");
            return null;
        }
        
        Log::info("🔍 TenantFinder - Subdomínio extraído: {$subdomain}");
        
        // Busca tenant pelo subdomínio
        $tenant = Tenant::where('subdominio', $subdomain)->first();

        if ($tenant) {
            Log::info("✅ TenantFinder - Tenant encontrado: ID={$tenant->id}, Nome={$tenant->nome}");
        } else {
            Log::error("❌ TenantFinder - Nenhum tenant encontrado para subdomínio: {$subdomain}");
        }
        
        return $tenant;
    }
    
    protected function extractSubdomain(string $host): ?string
    {
        // Remove porta se houver (ex: localhost:8000)
        $host = explode(':', $host)[0];
        
        // Define o domínio base em produção
        $baseDomain = env('MAIN_DOMAIN', 'eyiservicos.com.br');
        
        // Para domínios como: cliente1.nexasystem.eyiservicos.com.br
        // Remove o domínio base completo
        if (str_ends_with($host, ".{$baseDomain}")) {
            $subdomain = str_replace(".{$baseDomain}", '', $host);
            
            // Se ainda tiver pontos (ex: cliente1.nexasystem), pega apenas a primeira parte
            $parts = explode('.', $subdomain);
            return $parts[0];
        }
        
        // Fallback: pega a primeira parte do host
        $parts = explode('.', $host);
        return $parts[0] ?? null;
    }
}