<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AuthLayout from '@/layouts/AuthLayout.vue';
import axios from '@/lib/axios';
import { Head } from '@inertiajs/vue3';
import { AlertCircle, MessageSquare, RefreshCw, Send, User } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Message {
    id: string;
    sender: string;
    content: string;
    timestamp: Date | string;
    isBot: boolean;
    fromMe?: boolean;
    remoteJid?: string;
}

interface Conversation {
    id: string;
    name: string;
    phone: string;
    lastMessage: string;
    unread: number;
    timestamp: Date | string;
    remoteJid: string;
}

const props = defineProps<{
    instanceStatus?: any;
    isConfigured?: boolean;
}>();

// Declarar window.Echo para TypeScript
declare global {
    interface Window {
        Echo: any;
    }
}

const conversations = ref<Conversation[]>([]);
const selectedConversation = ref<string | null>(null);
const messages = ref<Message[]>([]);
const newMessage = ref('');
const loadingConversations = ref(false);
const sending = ref(false);
const messageCache = ref<Record<string, Message[]>>({});

const currentConversation = computed(() => conversations.value.find((c) => c.id === selectedConversation.value));

const selectConversation = async (id: string) => {
    selectedConversation.value = id;

    // Usar cache se disponível
    if (messageCache.value[id]) {
        messages.value = messageCache.value[id];
    } else {
        messages.value = [];
    }

    // Carregar mensagens em background
    await loadMessages(id);

    // Marcar como lida automaticamente
    const conversation = conversations.value.find((c) => c.id === id);
    if (conversation && conversation.unread > 0) {
        markConversationAsRead(id);
    }

    // Scroll para última mensagem
    setTimeout(scrollToBottom, 100);
};

const markConversationAsRead = async (conversationId: string) => {
    const conversation = conversations.value.find((c) => c.id === conversationId);
    if (!conversation) return;

    try {
        // Marcar localmente primeiro (otimista)
        conversation.unread = 0;

        // Pegar a última mensagem não lida
        const unreadMessages = messageCache.value[conversationId]?.filter((m) => !m.fromMe) || [];
        if (unreadMessages.length === 0) return;

        const lastMessage = unreadMessages[unreadMessages.length - 1];

        await axios.post('/chat/mark-read', {
            remoteJid: conversation.remoteJid,
            messageId: lastMessage.id,
        });

        console.log('Conversa marcada como lida:', conversationId);
    } catch (error) {
        console.error('Erro ao marcar como lida:', error);
    }
};

const loadConversations = async (silent = false) => {
    if (!silent) loadingConversations.value = true;
    try {
        const response = await axios.get('/chat/conversations');
        conversations.value = response.data;
    } catch (error: any) {
        console.error('Erro ao carregar conversas:', error);
    } finally {
        if (!silent) loadingConversations.value = false;
    }
};

const loadMessages = async (conversationId: string, silent = false) => {
    const conversation = conversations.value.find((c) => c.id === conversationId);
    if (!conversation) return;

    try {
        const response = await axios.get('/chat/messages', {
            params: { remoteJid: conversation.remoteJid },
        });

        const newMessages = response.data;
        messageCache.value[conversationId] = newMessages;

        // Só atualizar se for a conversa ativa
        if (selectedConversation.value === conversationId) {
            messages.value = newMessages;
            if (!silent) setTimeout(scrollToBottom, 100);
        }
    } catch (error) {
        console.error('Erro ao carregar mensagens:', error);
    }
};

const scrollToBottom = () => {
    const container = document.querySelector('.messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedConversation.value) return;

    const conversation = conversations.value.find((c) => c.id === selectedConversation.value);
    if (!conversation) return;

    const messageText = newMessage.value;
    newMessage.value = ''; // Limpar imediatamente para UX melhor

    sending.value = true;
    try {
        // Extrair apenas números do telefone
        const number = conversation.phone.replace(/\D/g, '');

        // Adicionar mensagem otimista
        const optimisticMessage: Message = {
            id: 'temp-' + Date.now(),
            sender: 'Você',
            content: messageText,
            timestamp: new Date().toISOString(),
            isBot: false,
            fromMe: true,
        };

        messages.value.push(optimisticMessage);
        scrollToBottom();

        const response = await axios.post('/chat/send', {
            number: number,
            message: messageText,
        });

        if (response.data.success) {
            // Atualizar com resposta real do servidor após 1 segundo
            setTimeout(() => {
                loadMessages(selectedConversation.value!, true);
            }, 1000);
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        alert('Erro ao enviar mensagem');
        newMessage.value = messageText; // Restaurar em caso de erro
    } finally {
        sending.value = false;
    }
};

const formatTime = (date: Date | string) => {
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    return new Intl.DateTimeFormat('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(dateObj);
};

onMounted(() => {
    if (props.isConfigured) {
        loadConversations();

        // Conectar ao WebSocket usando Echo
        if (window.Echo) {
            window.Echo.channel('chat')
                .listen('.message.new', (event: any) => {
                    const conversationId = event.conversationId;

                    // Atualizar cache
                    if (messageCache.value[conversationId]) {
                        messageCache.value[conversationId].push(event.message);
                    }

                    // Atualizar view se for conversa ativa
                    if (selectedConversation.value === conversationId) {
                        messages.value.push(event.message);
                        setTimeout(scrollToBottom, 100);
                    }

                    // Atualizar lista de conversas (silent)
                    loadConversations(true);
                })
                .listen('.message.read', (event: any) => {
                    // Atualizar contador de não lidas
                    const conversation = conversations.value.find((c) => c.id === event.conversationId);
                    if (conversation) {
                        conversation.unread = 0;
                    }
                });
        } else {
            console.warn('Echo não está disponível. WebSocket não funcionará.');
        }
    }
});

onUnmounted(() => {
    // Desconectar do WebSocket ao sair
    if (window.Echo) {
        window.Echo.leaveChannel('chat');
    }
});
</script>

<template>
    <Head>
        <title>Família Mogi - Chat</title>
        <meta name="description" content="Chat com clientes via WhatsApp" />
    </Head>

    <AuthLayout title="Chat">
        <!-- Alerta de configuração -->
        <Card v-if="!isConfigured" class="mb-4 border-yellow-300 bg-yellow-50">
            <CardContent class="flex items-start gap-3 p-4">
                <AlertCircle class="mt-0.5 h-5 w-5 text-yellow-600" />
                <div class="flex-1">
                    <h3 class="font-semibold text-yellow-900">Evolution API não configurada</h3>
                    <p class="mt-1 text-sm text-yellow-800">
                        Configure as variáveis de ambiente no arquivo <code class="rounded bg-yellow-100 px-1">.env</code>:
                    </p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-yellow-800">
                        <li><code class="rounded bg-yellow-100 px-1">EVOLUTION_API_URL</code> - URL da sua VPS</li>
                        <li><code class="rounded bg-yellow-100 px-1">EVOLUTION_API_KEY</code> - API Key da Evolution</li>
                        <li><code class="rounded bg-yellow-100 px-1">EVOLUTION_INSTANCE_NAME</code> - Nome da instância</li>
                    </ul>
                    <p class="mt-2 text-sm text-yellow-800">
                        Consulte <code class="rounded bg-yellow-100 px-1">QUICKSTART_EVOLUTION.md</code> para mais detalhes.
                    </p>
                </div>
            </CardContent>
        </Card>

        <div v-if="isConfigured" class="grid h-[calc(100vh-8rem)] grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Lista de Conversas -->
            <Card class="col-span-1">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <MessageSquare class="h-5 w-5" />
                            Conversas
                        </CardTitle>
                        <Button size="icon" variant="ghost" @click="loadConversations" :disabled="loadingConversations">
                            <RefreshCw :class="['h-4 w-4', loadingConversations ? 'animate-spin' : '']" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="h-[calc(100vh-12rem)] overflow-y-auto">
                        <div v-if="loadingConversations && conversations.length === 0" class="p-4 text-center text-gray-500">
                            Carregando conversas...
                        </div>
                        <div v-else-if="conversations.length === 0" class="p-4 text-center text-gray-500">Nenhuma conversa encontrada</div>
                        <div v-else class="space-y-1 p-2">
                            <div
                                v-for="conversation in conversations"
                                :key="conversation.id"
                                @click="selectConversation(conversation.id)"
                                :class="[
                                    'cursor-pointer rounded-lg p-3 transition-colors hover:bg-gray-100',
                                    selectedConversation === conversation.id ? 'border-l-4 border-green-600 bg-green-50' : '',
                                ]"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                            <User class="h-5 w-5 text-green-600" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ conversation.name }}</p>
                                            <p class="truncate text-sm text-gray-500">{{ conversation.lastMessage }}</p>
                                            <p class="text-xs text-gray-400">{{ conversation.phone }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="text-xs text-gray-400">{{ formatTime(conversation.timestamp) }}</span>
                                        <span
                                            v-if="conversation.unread > 0"
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-green-600 text-xs text-white"
                                        >
                                            {{ conversation.unread }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Área de Chat -->
            <Card class="col-span-1 md:col-span-2">
                <CardHeader v-if="currentConversation" class="border-b">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                            <User class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <CardTitle>{{ currentConversation.name }}</CardTitle>
                            <p class="text-sm text-gray-500">{{ currentConversation.phone }}</p>
                        </div>
                    </div>
                </CardHeader>
                <CardHeader v-else>
                    <CardTitle class="text-center text-gray-400">Selecione uma conversa</CardTitle>
                </CardHeader>

                <CardContent v-if="currentConversation" class="flex h-[calc(100vh-16rem)] flex-col p-0">
                    <!-- Mensagens -->
                    <div class="messages-container flex-1 overflow-y-auto p-4">
                        <div class="space-y-4">
                            <div
                                v-for="message in messages"
                                :key="message.id"
                                :class="['flex', message.sender === 'Você' ? 'justify-end' : 'justify-start']"
                            >
                                <div
                                    :class="[
                                        'max-w-[70%] rounded-lg p-3',
                                        message.sender === 'Você'
                                            ? 'bg-green-600 text-white'
                                            : message.isBot
                                              ? 'bg-blue-100 text-gray-900'
                                              : 'bg-gray-100 text-gray-900',
                                    ]"
                                >
                                    <p class="mb-1 text-sm font-medium" v-if="message.isBot">🤖 IA Assistant</p>
                                    <p>{{ message.content }}</p>
                                    <p class="mt-1 text-xs opacity-70">{{ formatTime(message.timestamp) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input de Mensagem -->
                    <div class="border-t p-4">
                        <form @submit.prevent="sendMessage" class="flex gap-2">
                            <Input v-model="newMessage" placeholder="Digite sua mensagem..." class="flex-1" />
                            <Button type="submit" size="icon" :disabled="!newMessage.trim() || sending">
                                <Send class="h-4 w-4" />
                            </Button>
                        </form>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthLayout>
</template>
