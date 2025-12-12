<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected ?string $baseUrl;
    protected ?string $apiKey;
    protected ?string $instanceName;

    public function __construct()
    {
        $this->baseUrl = config('services.evolution.url');
        $this->apiKey = config('services.evolution.api_key');
        $this->instanceName = config('services.evolution.instance_name');
    }

    /**
     * Verificar se o serviço está configurado
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey) && !empty($this->instanceName);
    }

    /**
     * Headers padrão para requisições
     */
    protected function headers(): array
    {
        return [
            'apikey' => $this->apiKey ?? '',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Buscar todas as conversas/chats
     * Evolution API v2.3.6 usa POST para buscar mensagens
     */
    public function getChats(int $limit = 200)
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/chat/findMessages/{$this->instanceName}", [
                    'limit' => $limit,
                ]);

            if (!$response->successful()) {
                Log::error('Erro ao buscar chats da Evolution API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }

            $data = $response->json();
            
            // Extrair records do response
            $messages = $data['messages']['records'] ?? [];
            
            // Verificar se retornou dados
            if (empty($messages)) {
                return [];
            }
            
            // Agrupar mensagens por remoteJid para criar lista de conversas
            $chats = [];
            $grouped = collect($messages)->groupBy(function ($msg) {
                return $msg['key']['remoteJid'] ?? '';
            });

            foreach ($grouped as $remoteJid => $messages) {
                // Pular status e conversas inválidas
                if (str_contains($remoteJid, 'status@broadcast') || empty($remoteJid)) {
                    continue;
                }

                $lastMessage = $messages->first();
                
                // Contar mensagens não lidas (do cliente, não suas, sem status de leitura)
                $unreadCount = $messages->filter(function ($msg) {
                    $fromMe = ($msg['key']['fromMe'] ?? 0) == 1;
                    $status = $msg['status'] ?? 'sent';
                    
                    // Mensagem não é sua E ainda não foi lida
                    return !$fromMe && !in_array($status, ['read', 'played']);
                })->count();

                $chats[] = [
                    'id' => $remoteJid,
                    'remoteJid' => $remoteJid,
                    'name' => $lastMessage['pushName'] ?? $this->formatPhoneNumber($remoteJid),
                    'lastMessage' => $this->getMessageText($lastMessage),
                    'timestamp' => $lastMessage['messageTimestamp'] ?? time(),
                    'unreadCount' => $unreadCount,
                ];
            }

            // Ordenar por timestamp decrescente
            return collect($chats)->sortByDesc('timestamp')->values()->toArray();
        } catch (\Exception $e) {
            Log::error('Exceção ao buscar chats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar mensagens de um chat específico
     */
    public function getMessages(string $remoteJid, int $limit = 50)
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/chat/findMessages/{$this->instanceName}", [
                    'where' => [
                        'key' => [
                            'remoteJid' => $remoteJid,
                        ],
                    ],
                    'limit' => $limit,
                ]);

            if (!$response->successful()) {
                Log::error('Erro ao buscar mensagens da Evolution API', [
                    'remoteJid' => $remoteJid,
                    'status' => $response->status()
                ]);
                return [];
            }

            $data = $response->json();
            
            // Com where clause, retorna objeto {messages: {records: [...]}}
            $messages = $data['messages']['records'] ?? [];

            return collect($messages)->map(function ($msg) {
                return [
                    'id' => $msg['key']['id'] ?? '',
                    'fromMe' => ($msg['key']['fromMe'] ?? false) == 1,
                    'content' => $this->getMessageText($msg),
                    'timestamp' => $msg['messageTimestamp'] ?? time(),
                    'messageType' => $msg['messageType'] ?? 'text',
                    'status' => $msg['status'] ?? 'sent',
                ];
            })->sortBy('timestamp')->values()->toArray();
        } catch (\Exception $e) {
            Log::error('Exceção ao buscar mensagens: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Extrair texto da mensagem
     */
    protected function getMessageText(array $message): string
    {
        $msg = $message['message'] ?? [];
        
        if (isset($msg['conversation'])) {
            return $msg['conversation'];
        }
        
        if (isset($msg['extendedTextMessage']['text'])) {
            return $msg['extendedTextMessage']['text'];
        }

        $type = $message['messageType'] ?? 'unknown';
        return match ($type) {
            'imageMessage' => '📷 Imagem',
            'videoMessage' => '🎥 Vídeo',
            'audioMessage' => '🎵 Áudio',
            'documentMessage' => '📄 Documento',
            default => '💬 Mensagem',
        };
    }

    /**
     * Formatar número de telefone
     */
    protected function formatPhoneNumber(string $jid): string
    {
        $number = str_replace(['@s.whatsapp.net', '@g.us'], '', $jid);
        
        // Se for grupo, manter ID
        if (str_contains($jid, '@g.us')) {
            return "Grupo {$number}";
        }

        // Formatar número brasileiro
        if (preg_match('/^55(\d{2})(\d{5})(\d{4})$/', $number, $matches)) {
            return "+55 ({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }

        return "+$number";
    }

    /**
     * Enviar mensagem de texto
     */
    public function sendTextMessage(string $number, string $text)
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/message/sendText/{$this->instanceName}", [
                    'number' => $number,
                    'text' => $text
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao enviar mensagem', [
                'number' => $number,
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar mensagem: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Marcar mensagem como lida
     */
    public function markAsRead(string $remoteJid, string $messageId)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            // Evolution API v2 - endpoint para marcar como lida
            $response = Http::withHeaders($this->headers())
                ->put("{$this->baseUrl}/chat/markMessageAsRead/{$this->instanceName}", [
                    'readMessages' => [
                        [
                            'remoteJid' => $remoteJid,
                            'id' => $messageId,
                            'fromMe' => false
                        ]
                    ]
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Exceção ao marcar como lida: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar contato por número
     */
    public function getContact(string $number)
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/chat/findContact/{$this->instanceName}", [
                    'number' => $number
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao buscar contato: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar status da instância
     */
    public function getInstanceStatus()
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instanceName}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao verificar status: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Enviar mensagem para o webhook do n8n para processamento da IA
     */
    public function sendToN8n(array $messageData)
    {
        $n8nWebhook = config('services.n8n.webhook_url');
        
        if (!$n8nWebhook) {
            return null;
        }

        try {
            $response = Http::post($n8nWebhook, $messageData);
            
            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar para n8n: ' . $e->getMessage());
            return null;
        }
    }
}
