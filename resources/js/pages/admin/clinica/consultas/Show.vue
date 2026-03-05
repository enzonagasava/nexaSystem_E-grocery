<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Consulta } from '@/types';

const page = usePage();
const consulta = page.props.consulta as Consulta;

function getStatusClass(status: string): string {
    switch (status) {
        case 'agendada':
            return 'bg-blue-100 text-blue-800';
        case 'em-andamento':
            return 'bg-yellow-100 text-yellow-800';
        case 'realizada':
            return 'bg-green-100 text-green-800';
        case 'cancelada':
            return 'bg-red-100 text-red-800';
        default:
<<<<<<< HEAD
            return ' text-gray-800';
=======
            return 'bg-gray-100 text-gray-800';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
}

function getStatusLabel(status: string): string {
    switch (status) {
        case 'agendada':
            return 'Agendada';
        case 'em-andamento':
            return 'Em Andamento';
        case 'realizada':
            return 'Realizada';
        case 'cancelada':
            return 'Cancelada';
        default:
            return status;
    }
}
</script>

<template>
    <Head>
        <title>Detalhes da Consulta - Clínica</title>
    </Head>

    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
<<<<<<< HEAD
                <div class="overflow-hidden  shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200  p-6">
=======
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-800">Detalhes da Consulta</h2>
                            <div class="flex gap-3">
                                <Link :href="route('admin.clinica.consultas.edit', consulta.id)">
                                    <Button variant="outline">Editar</Button>
                                </Link>
                                <Link :href="route('admin.clinica.consultas.index')">
                                    <Button variant="ghost">Voltar</Button>
                                </Link>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-6">
                            <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusClass(consulta.status)]">
                                {{ getStatusLabel(consulta.status) }}
                            </span>
                        </div>

                        <!-- Dados da Consulta -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Dados da Consulta</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-gray-600">Paciente:</span>
                                    <p class="text-gray-900">{{ consulta.paciente?.nome }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Telefone:</span>
                                    <p class="text-gray-900">{{ consulta.paciente?.telefone }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Data:</span>
                                    <p class="text-gray-900">{{ consulta.data_formatada }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Horário:</span>
                                    <p class="text-gray-900">{{ consulta.horario_formatado }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Tipo:</span>
                                    <p class="text-gray-900 capitalize">{{ consulta.tipo }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Valor:</span>
                                    <p class="text-gray-900">R$ {{ consulta.valor ? Number(consulta.valor).toFixed(2).replace('.', ',') : '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div v-if="consulta.motivo" class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Motivo da Consulta</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ consulta.motivo }}</p>
                        </div>

                        <!-- Diagnóstico -->
                        <div v-if="consulta.diagnostico" class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Diagnóstico</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ consulta.diagnostico }}</p>
                        </div>

                        <!-- Prescrição -->
                        <div v-if="consulta.prescricao" class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Prescrição</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ consulta.prescricao }}</p>
                        </div>

                        <!-- Observações -->
                        <div v-if="consulta.observacoes" class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-700 border-b pb-2">Observações</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ consulta.observacoes }}</p>
                        </div>

                        <!-- Metadados -->
                        <div class="text-sm text-gray-500">
                            Cadastrada em: {{ consulta.created_at_formatted }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
