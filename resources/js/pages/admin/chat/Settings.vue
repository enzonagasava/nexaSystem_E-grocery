<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface ConfiguracaoIa {
    id: number;
    bot_ativo: boolean;
    tom_voz: 'amigavel' | 'profissional';
    mensagem_boas_vindas: string | null;
    mensagem_fora_horario: string | null;
    timer_ausencia: number;
    bloquear_bot: boolean;
    bloqueio_ate: string | null;
}

interface RespostaRapida {
    id: number;
    atalho: string;
    mensagem: string;
    ativo: boolean;
}

const props = defineProps<{
    config: ConfiguracaoIa;
    respostasRapidas: RespostaRapida[];
}>();

// Form para configuração da IA
const configForm = useForm({
    bot_ativo: props.config.bot_ativo,
    tom_voz: props.config.tom_voz,
    mensagem_boas_vindas: props.config.mensagem_boas_vindas || '',
    mensagem_fora_horario: props.config.mensagem_fora_horario || '',
    timer_ausencia: props.config.timer_ausencia,
    bloquear_bot: props.config.bloquear_bot,
    bloqueio_ate: props.config.bloqueio_ate || '',
});

const submitConfig = () => {
    configForm.put(route('admin.chat.settings.config'), {
        preserveScroll: true,
    });
};

// Respostas rápidas
const showRespostaForm = ref(false);
const editingResposta = ref<RespostaRapida | null>(null);

const respostaForm = useForm({
    atalho: '',
    mensagem: '',
    ativo: true,
});

const openRespostaForm = (resposta?: RespostaRapida) => {
    if (resposta) {
        editingResposta.value = resposta;
        respostaForm.atalho = resposta.atalho;
        respostaForm.mensagem = resposta.mensagem;
        respostaForm.ativo = resposta.ativo;
    } else {
        editingResposta.value = null;
        respostaForm.reset();
    }
    showRespostaForm.value = true;
};

const submitResposta = () => {
    if (editingResposta.value) {
        respostaForm.put(route('admin.chat.settings.respostas.update', editingResposta.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showRespostaForm.value = false;
                respostaForm.reset();
            },
        });
    } else {
        respostaForm.post(route('admin.chat.settings.respostas.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showRespostaForm.value = false;
                respostaForm.reset();
            },
        });
    }
};

const deleteResposta = (id: number) => {
    if (confirm('Tem certeza que deseja excluir esta resposta rápida?')) {
        useForm({}).delete(route('admin.chat.settings.respostas.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AuthLayout title="Configurações do Chat">
        <div class="space-y-6">
            <!-- Configuração do Bot -->
            <Card>
                <CardHeader>
                    <CardTitle>Configuração do Bot de IA</CardTitle>
                    <CardDescription>Configure o comportamento do assistente virtual</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitConfig" class="space-y-6">
                        <div class="space-y-2">
                            <Label>Ativar Bot</Label>
                            <p class="text-sm text-gray-500">Habilitar respostas automáticas via IA</p>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" v-model="configForm.bot_ativo" class="peer sr-only" />
                                    <div
                                        class="peer-focus:ring-4q peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-green-600 peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full"
                                    ></div>
                                </label>
                                <span class="text-sm font-medium" :class="configForm.bot_ativo ? 'text-green-500' : 'text-gray-500'">
                                    {{ configForm.bot_ativo ? 'Ativado' : 'Desativado' }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Tom de Voz</Label>
                            <select
                                v-model="configForm.tom_voz"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                <option value="amigavel">Amigável</option>
                                <option value="profissional">Profissional</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label>Mensagem de Boas-Vindas</Label>
                            <textarea
                                v-model="configForm.mensagem_boas_vindas"
                                placeholder="Digite a mensagem de boas-vindas..."
                                rows="3"
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Mensagem Fora de Horário</Label>
                            <textarea
                                v-model="configForm.mensagem_fora_horario"
                                placeholder="Digite a mensagem para fora do horário..."
                                rows="3"
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Timer de Ausência (segundos)</Label>
                            <Input v-model.number="configForm.timer_ausencia" type="number" min="0" placeholder="300" />
                            <p class="text-sm text-gray-500">Tempo sem resposta antes do bot assumir</p>
                        </div>

                        <!-- <div class="flex items-center justify-between">
                            <div>
                                <Label>Bloquear Bot Temporariamente</Label>
                                <p class="text-sm text-gray-500">Desativar bot por tempo determinado</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="configForm.bloquear_bot" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div> -->

                        <div v-if="configForm.bloquear_bot" class="space-y-2">
                            <Label>Bloqueio Até</Label>
                            <Input v-model="configForm.bloqueio_ate" type="datetime-local" />
                        </div>

                        <Button type="submit" :disabled="configForm.processing"> Salvar Configurações </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Respostas Rápidas -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Respostas Rápidas</CardTitle>
                            <CardDescription>Atalhos para mensagens frequentes</CardDescription>
                        </div>
                        <Button @click="openRespostaForm()" size="sm">
                            <Plus class="mr-2 h-4 w-4" />
                            Nova Resposta
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="showRespostaForm" class="mb-6 rounded-lg border p-4">
                        <form @submit.prevent="submitResposta" class="space-y-4">
                            <div class="space-y-2">
                                <Label>Atalho</Label>
                                <Input v-model="respostaForm.atalho" placeholder="/oi" maxlength="50" />
                            </div>

                            <div class="space-y-2">
                                <Label>Mensagem</Label>
                                <textarea
                                    v-model="respostaForm.mensagem"
                                    placeholder="Digite a mensagem..."
                                    rows="3"
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <Label>Ativo</Label>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" v-model="respostaForm.ativo" class="peer sr-only" />
                                    <div
                                        class="peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-green-600 peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full"
                                    ></div>
                                </label>
                            </div>

                            <div class="flex gap-2">
                                <Button type="submit" :disabled="respostaForm.processing">
                                    {{ editingResposta ? 'Atualizar' : 'Criar' }}
                                </Button>
                                <Button type="button" variant="outline" @click="showRespostaForm = false"> Cancelar </Button>
                            </div>
                        </form>
                    </div>

                    <div class="space-y-2">
                        <div v-for="resposta in respostasRapidas" :key="resposta.id" class="flex items-center justify-between rounded-lg border p-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <code class="rounded bg-gray-100 px-2 py-1 font-mono text-sm">
                                        {{ resposta.atalho }}
                                    </code>
                                    <span v-if="!resposta.ativo" class="text-xs text-gray-500">(Inativo)</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ resposta.mensagem }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Button @click="openRespostaForm(resposta)" variant="outline" size="sm"> Editar </Button>
                                <Button @click="deleteResposta(resposta.id)" variant="destructive" size="sm">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <p v-if="respostasRapidas.length === 0" class="py-8 text-center text-gray-500">Nenhuma resposta rápida cadastrada</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthLayout>
</template>
