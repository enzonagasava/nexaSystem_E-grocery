<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Agendamento } from '@/types';

const page = usePage();
const agendamento = page.props.agendamento as Agendamento;

function getStatusClass(status: string): string {
    switch (status) {
        case 'pendente':
            return 'bg-yellow-100 text-yellow-800';
        case 'confirmado':
            return 'bg-blue-100 text-blue-800';
        case 'realizado':
            return 'bg-green-100 text-green-800';
        case 'cancelado':
            return 'bg-red-100 text-red-800';
        default:
            return ' text-gray-800';
    }
}

function getStatusLabel(status: string): string {
    switch (status) {
        case 'pendente':
            return 'Pendente';
        case 'confirmado':
            return 'Confirmado';
        case 'realizado':
            return 'Realizado';
        case 'cancelado':
            return 'Cancelado';
        default:
            return status;
    }
}
</script>

<template>
    <Head>
        <title>Detalhes do Agendamento - Clínica</title>
    </Head>

    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden  shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200  p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-800">Detalhes do Agendamento</h2>
                            <div class="flex gap-3">
                                <Link :href="route('admin.clinica.agendamentos.edit', agendamento.id)">
                                    <Button variant="outline">Editar</Button>
                                </Link>
                                <Link :href="route('admin.clinica.agendamentos.index')">
                                    <Button variant="ghost">Voltar</Button>
                                </Link>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-6">
                            <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusClass(agendamento.status)]">
                                {{ getStatusLabel(agendamento.status) }}
                            </span>
                        </div>

                        <!-- Dados do Agendamento -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Dados do Agendamento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-gray-600">Paciente:</span>
                                    <p class="text-gray-900">{{ agendamento.paciente?.nome }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Telefone:</span>
                                    <p class="text-gray-900">{{ agendamento.paciente?.telefone }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Data:</span>
                                    <p class="text-gray-900">{{ agendamento.data_formatada }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Hora:</span>
                                    <p class="text-gray-900">{{ agendamento.hora_formatada }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Duração:</span>
                                    <p class="text-gray-900">{{ agendamento.duracao_minutos }} minutos</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Tipo:</span>
                                    <p class="text-gray-900 capitalize">{{ agendamento.tipo }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div v-if="agendamento.observacoes" class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Observações</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ agendamento.observacoes }}</p>
                        </div>

                        <!-- Metadados -->
                        <div class="text-sm text-gray-500">
                            Cadastrado em: {{ agendamento.created_at_formatted }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
