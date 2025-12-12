<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EvolutionApiService;
use App\Events\NewChatMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    protected EvolutionApiService $evolutionApi;

    public function __construct(EvolutionApiService $evolutionApi)
    {
        $this->evolutionApi = $evolutionApi;
    }

    /**
     * Página principal do chat
     */
    public function index()
    {
        $instanceStatus = null;
        $isConfigured = $this->evolutionApi->isConfigured();

        if ($isConfigured) {
            $instanceStatus = $this->evolutionApi->getInstanceStatus();
        }

        return Inertia::render('admin/chat/Chat', [
            'instanceStatus' => $instanceStatus,
            'isConfigured' => $isConfigured
        ]);
    }

    /**
     * Buscar todas as conversas
     */
    public function getConversations()
    {
        $chats = $this->evolutionApi->getChats();
        
        // Formatar dados para o frontend
        $conversations = collect($chats)->map(function ($chat) {
            return [
                'id' => $chat['id'] ?? $chat['remoteJid'] ?? '',
                'name' => $chat['name'] ?? 'Sem nome',
                'phone' => $this->formatPhone($chat['remoteJid'] ?? ''),
                'lastMessage' => $chat['lastMessage'] ?? '',
                'unread' => $chat['unreadCount'] ?? 0,
                'timestamp' => isset($chat['timestamp']) 
                    ? date('Y-m-d H:i:s', $chat['timestamp'])
                    : now(),
                'remoteJid' => $chat['remoteJid'] ?? ''
            ];
        })->toArray();

        return response()->json($conversations);
    }

    /**
     * Buscar mensagens de uma conversa
     */
    public function getMessages(Request $request)
    {
        $remoteJid = $request->input('remoteJid');
        $limit = $request->input('limit', 50);

        $messages = $this->evolutionApi->getMessages($remoteJid, $limit);

        // Formatar mensagens para o frontend
        $formattedMessages = collect($messages)->map(function ($msg) {
            return [
                'id' => $msg['id'] ?? '',
                'sender' => $msg['fromMe'] ? 'Você' : 'Cliente',
                'content' => $msg['content'] ?? '',
                'timestamp' => isset($msg['timestamp']) 
                    ? date('Y-m-d H:i:s', $msg['timestamp'])
                    : now(),
                'isBot' => false,
                'fromMe' => $msg['fromMe'] ?? false,
            ];
        })->toArray();

        return response()->json($formattedMessages);
    }

    /**
     * Enviar mensagem
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'number' => 'required|string',
            'message' => 'required|string'
        ]);

        $result = $this->evolutionApi->sendTextMessage(
            $request->number,
            $request->message
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao enviar mensagem'
        ], 500);
    }

    /**
     * Marcar como lida
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'remoteJid' => 'required|string',
            'messageId' => 'required|string'
        ]);

        $success = $this->evolutionApi->markAsRead(
            $request->remoteJid,
            $request->messageId
        );

        return response()->json([
            'success' => $success
        ]);
    }

    /**
     * Webhook para receber mensagens da Evolution API
     */
    public function webhook(Request $request)
    {
        $data = $request->all();

        // Verificar se é uma mensagem nova
        if (isset($data['event']) && $data['event'] === 'messages.upsert') {
            $message = $data['data'] ?? [];
            
            // Broadcast para todos os clientes conectados
            if (isset($message['key']['remoteJid'])) {
                $formattedMessage = [
                    'id' => $message['key']['id'] ?? '',
                    'sender' => ($message['key']['fromMe'] ?? false) ? 'Você' : 'Cliente',
                    'content' => $this->extractMessageContent($message),
                    'timestamp' => isset($message['messageTimestamp']) 
                        ? date('Y-m-d H:i:s', $message['messageTimestamp'])
                        : now(),
                    'isBot' => false,
                    'fromMe' => $message['key']['fromMe'] ?? false,
                ];

                broadcast(new NewChatMessage(
                    $formattedMessage,
                    $message['key']['remoteJid']
                ))->toOthers();
            }
            
            // Se não for mensagem sua, enviar para n8n processar com IA
            if (!($message['key']['fromMe'] ?? false)) {
                $this->evolutionApi->sendToN8n($message);
            }
        }
        
        // Verificar se é atualização de status de leitura
        if (isset($data['event']) && $data['event'] === 'messages.update') {
            // Broadcast para atualizar contador de não lidas
            // O frontend vai recarregar a lista de conversas
        }

        return response()->json(['success' => true]);
    }

    /**
     * Extrair conteúdo da mensagem
     */
    protected function extractMessageContent($message): string
    {
        if (isset($message['message']['conversation'])) {
            return $message['message']['conversation'];
        }

        if (isset($message['message']['extendedTextMessage']['text'])) {
            return $message['message']['extendedTextMessage']['text'];
        }

        if (isset($message['message']['imageMessage']['caption'])) {
            return '📷 ' . $message['message']['imageMessage']['caption'];
        }

        if (isset($message['message']['videoMessage']['caption'])) {
            return '🎥 ' . $message['message']['videoMessage']['caption'];
        }

        return '[Mensagem de mídia]';
    }

    /**
     * Formatar número de telefone
     */
    protected function formatPhone(string $remoteJid): string
    {
        // Remove @s.whatsapp.net
        $phone = str_replace('@s.whatsapp.net', '', $remoteJid);
        
        // Formata para padrão brasileiro se for BR
        if (strlen($phone) === 13 && str_starts_with($phone, '55')) {
            return preg_replace('/^55(\d{2})(\d{5})(\d{4})$/', '+55 ($1) $2-$3', $phone);
        }

        return $phone;
    }
}
