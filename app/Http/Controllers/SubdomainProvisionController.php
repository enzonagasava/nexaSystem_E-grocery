<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubdomainProvisionController extends Controller
{
    // Cria subdomínio/sub.subdominio via EasyPanel e Cloudflared
    public function create(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string',
            'third_level' => 'nullable|string',
            'tenant_id' => 'required|integer',
        ]);

        $sub = $request->input('subdomain');
        $third = $request->input('third_level');
        $tenantId = $request->input('tenant_id');

        // 1. Chamar EasyPanel API para criar domínio (retorna sessionToken para o tunnel)
        $easypanelResponse = $this->provisionEasyPanel($sub, $third);
        if (!$easypanelResponse['success']) {
            return response()->json(['error' => 'EasyPanel: ' . $easypanelResponse['message']], 500);
        }

        // 2. Criar regra de tunnel no EasyPanel (DNS/subdomínio) em vez de chamar Cloudflare direto
        $sessionToken = $easypanelResponse['sessionToken'] ?? null;
        if ($sessionToken) {
            $tunnelResponse = $this->provisionEasyPanelTunnelRule($sub, $sessionToken);
            if (!$tunnelResponse['success']) {
                return response()->json(['error' => 'EasyPanel Tunnel: ' . $tunnelResponse['message']], 500);
            }
        }

        // 3. Sucesso
        return response()->json(['message' => 'Subdomínio criado com sucesso!']);
    }

    private function provisionEasyPanel($sub, $third = null)
    {
        try {
            $domain = $third ? "$third.$sub" : $sub;
            $apiUrl = config('services.easypanel.api_url') ?: env('EASYPANEL_API_URL');
            $mainDomain = config('services.easypanel.main_domain') ?: env('MAIN_DOMAIN');

            if (empty($apiUrl) || empty($mainDomain)) {
                return ['success' => false, 'message' => 'EASYPANEL_API_URL ou MAIN_DOMAIN não configurados no .env'];
            }

            $email = config('services.easypanel.email') ?: env('EASYPANEL_EMAIL');
            $password = config('services.easypanel.password') ?: env('EASYPANEL_PASSWORD');
            if (empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'EASYPANEL_EMAIL e EASYPANEL_PASSWORD devem estar configurados no .env'];
            }

            // 1. Login (sem Bearer): obtém o token que será usado no header das próximas requisições
            $loginPayload = [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                    'rememberMe' => false,
                    'code' => 'string',
                ],
            ];
            $loginResponse = Http::withHeaders([
                'accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/trpc/auth.login', $loginPayload);

            Log::info('EasyPanel auth.login', [
                'status' => $loginResponse->status(),
                'body' => $loginResponse->body(),
            ]);

            if (!$loginResponse->successful()) {
                return ['success' => false, 'message' => 'Login EasyPanel: ' . $loginResponse->body()];
            }

            $loginData = $loginResponse->json();
            $sessionToken = $loginData['result']['data']['json']['token']
                ?? $loginData['result']['data']['token']
                ?? $loginData['result']['token']
                ?? $loginData['data']['json']['token']
                ?? $loginData['data']['token']
                ?? $loginData['token']
                ?? null;
            if ($sessionToken === null && !empty($loginData['result']['data']['json'][0]['token'])) {
                $sessionToken = $loginData['result']['data']['json'][0]['token'];
            }
            $sessionToken = $sessionToken !== null ? (string) trim((string) $sessionToken) : null;

            if (!$sessionToken) {
                Log::warning('EasyPanel login response structure (token not found)', ['response' => $loginData]);
                return ['success' => false, 'message' => 'Token de sessão não retornado pelo EasyPanel. Verifique os logs para a estrutura da resposta.'];
            }

            // 2. Criar domínio no mesmo formato do GET (host, serviceDestination, certificateResolver "", etc.)
            $host = $domain . '.' . $mainDomain;
            $id = str_replace('.', '-', $host);
            $path = env('EASYPANEL_DOMAIN_PATH', '/');
            $certificateResolver = env('EASYPANEL_DOMAIN_CERTIFICATE_RESOLVER', '');
            $https = filter_var(env('EASYPANEL_DOMAIN_HTTPS', true), FILTER_VALIDATE_BOOLEAN);
            $wildcard = filter_var(env('EASYPANEL_DOMAIN_WILDCARD', false), FILTER_VALIDATE_BOOLEAN);
            $projectName = env('EASYPANEL_TUNNEL_PROJECT_NAME');
            $serviceName = env('EASYPANEL_TUNNEL_SERVICE_NAME');
            $serviceProtocol = env('EASYPANEL_TUNNEL_INTERNAL_PROTOCOL', 'http');
            $servicePort = (int) env('EASYPANEL_TUNNEL_INTERNAL_PORT', 80);

            $createPayload = [
                'json' => [
                    'id' => $id,
                    'https' => $https,
                    'host' => $host,
                    'path' => $path,
                    'middlewares' => [],
                    'certificateResolver' => $certificateResolver,
                    'wildcard' => $wildcard,
                    'destinationType' => 'service',
                    'serviceDestination' => [
                        'protocol' => $serviceProtocol,
                        'port' => $servicePort,
                        'path' => $path,
                        'projectName' => $projectName,
                        'serviceName' => $serviceName,
                        'composeService' => env('EASYPANEL_DOMAIN_COMPOSE_SERVICE', ''),
                    ],
                ],
            ];
            Log::info('EasyPanel domains.createDomain request', ['payload' => $createPayload]);
            $createResponse = Http::withHeaders([
                'accept' => '*/*',
                'Authorization' => 'Bearer ' . $sessionToken,
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/trpc/domains.createDomain', $createPayload);

            Log::info('EasyPanel domains.createDomain', [
                'status' => $createResponse->status(),
                'body' => $createResponse->body(),
            ]);

            if (!$createResponse->successful()) {
                return ['success' => false, 'message' => $createResponse->body()];
            }
            return ['success' => true, 'sessionToken' => $sessionToken];
        } catch (\Exception $e) {
            Log::error('EasyPanel API error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function provisionEasyPanelTunnelRule($sub, $sessionToken)
    {
        try {
            $apiUrl = rtrim(env('EASYPANEL_API_URL'), '/');
            $subdomain = $sub;

            $projectName = env('EASYPANEL_TUNNEL_PROJECT_NAME');
            $serviceName = env('EASYPANEL_TUNNEL_SERVICE_NAME');
            $path = env('EASYPANEL_TUNNEL_PATH', '/');
            $internalProtocol = env('EASYPANEL_TUNNEL_INTERNAL_PROTOCOL', 'http');
            $internalPort = (int) env('EASYPANEL_TUNNEL_INTERNAL_PORT', 0);

            if (empty($apiUrl) || empty($projectName) || empty($serviceName)) {
                return ['success' => false, 'message' => 'EASYPANEL_API_URL, EASYPANEL_TUNNEL_PROJECT_NAME ou EASYPANEL_TUNNEL_SERVICE_NAME não configurados no .env'];
            }

            $bearerToken = (string) trim($sessionToken);

            // 1. GET cloudflareTunnel.listZones para obter zoneId e domain (name) do primeiro item
            $zonesResponse = Http::withHeaders([
                'accept' => '*/*',
                'Authorization' => 'Bearer ' . $bearerToken,
            ])->get($apiUrl . '/trpc/cloudflareTunnel.listZones');

            if (!$zonesResponse->successful()) {
                Log::warning('EasyPanel cloudflareTunnel.listZones failed', [
                    'status' => $zonesResponse->status(),
                    'body' => $zonesResponse->body(),
                ]);
                return ['success' => false, 'message' => 'Falha ao listar zones: ' . $zonesResponse->body()];
            }

            $zonesData = $zonesResponse->json();
            $zonesList = $zonesData['result']['data']['json'] ?? $zonesData['result']['data'] ?? $zonesData['data']['json'] ?? $zonesData['data'] ?? $zonesData['json'] ?? null;
            if (empty($zonesList) || !is_array($zonesList)) {
                return ['success' => false, 'message' => 'Resposta de listZones sem lista de zones'];
            }
            $firstZone = $zonesList[0] ?? null;
            if (empty($firstZone) || empty($firstZone['id']) || empty($firstZone['name'])) {
                return ['success' => false, 'message' => 'Primeiro item de listZones não contém id ou name'];
            }
            $zoneId = (string) $firstZone['id'];
            $domain = (string) $firstZone['name'];

            Log::info('EasyPanel cloudflareTunnel.listZones', ['zoneId' => $zoneId, 'domain' => $domain]);

            // 2. POST createTunnelRule com zoneId e domain obtidos (sem "code" no body; Bearer no header)
            $jsonPayload = [
                'projectName' => $projectName,
                'serviceName' => $serviceName,
                'subdomain' => $subdomain,
                'domain' => $domain,
                'path' => $path,
                'internalProtocol' => $internalProtocol,
                'internalPort' => $internalPort,
                'zoneId' => $zoneId,
                "proxied"=> false
            ];
            $payload = ['json' => $jsonPayload];
            $url = $apiUrl . '/trpc/cloudflareTunnel.createTunnelRule';

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            Log::info('EasyPanel cloudflareTunnel.createTunnelRule', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                return ['success' => true];
            }
            return ['success' => false, 'message' => $response->body()];
        } catch (\Exception $e) {
            Log::error('EasyPanel createTunnelRule error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function provisionCloudflared($sub, $third = null)
    {
        // Deprecated: uso da API do Cloudflare direto; preferir provisionEasyPanelTunnelRule (EasyPanel).
        try {
            $domain = $third ? "$third.$sub" : $sub;
            $response = Http::withToken(env('CLOUDFLARED_API_TOKEN'))
                ->post(env('CLOUDFLARED_API_URL') . '/dns', [
                    'name' => $domain . '.' . env('MAIN_DOMAIN'),
                    'type' => 'CNAME',
                    'content' => env('CLOUDFLARED_TUNNEL_DOMAIN'),
                ]);
            if ($response->successful()) {
                return ['success' => true];
            }
            return ['success' => false, 'message' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Cloudflared API error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
