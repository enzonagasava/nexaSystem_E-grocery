<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router, Link } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { ConsultaListItem } from '@/types';

const props = defineProps<{
    show: boolean;
    consulta: ConsultaListItem | null;
}>();

const emit = defineEmits(['close', 'deleted']);

const close = () => emit('close');

watch(
    () => props.show,
    (isVisible) => {
        document.body.style.overflow = isVisible ? 'hidden' : '';
    },
);

const deleteConsulta = (consultaId: number) => {
    if (confirm(`Tem certeza de que deseja excluir a consulta ID: ${consultaId}?`)) {
        router.delete(route('admin.clinica.consultas.destroy', consultaId), {
            onSuccess: () => {
                emit('deleted', consultaId);
                close();
            },
            onError: () => {
                alert(`Erro ao excluir a consulta.`);
            },
        });
    }
};

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
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-xs" @click.self="close">
<<<<<<< HEAD
        <div class="relative w-full max-w-lg rounded-lg border  p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3)]">
            <Button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" @click="close">✕</Button>
=======
        <div class="relative w-full max-w-lg rounded-lg border bg-white p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3)]">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" @click="close">✕</button>
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

            <h2 class="mb-4 text-xl font-bold">Detalhes da Consulta</h2>

            <div class="space-y-3">
                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Paciente:</span>
                    <span class="col-span-2 text-gray-800">{{ consulta?.paciente_nome }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Telefone:</span>
                    <span class="col-span-2 text-gray-800">{{ consulta?.paciente_telefone }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Data:</span>
                    <span class="col-span-2 text-gray-800">{{ consulta?.data_formatada }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Horário:</span>
                    <span class="col-span-2 text-gray-800">{{ consulta?.horario_formatado }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Tipo:</span>
                    <span class="col-span-2 text-gray-800">{{ consulta?.tipo }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Status:</span>
                    <span class="col-span-2">
                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClass(consulta?.status || '')]">
                            {{ getStatusLabel(consulta?.status || '') }}
                        </span>
                    </span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Valor:</span>
                    <span class="col-span-2 text-gray-800">R$ {{ consulta?.valor }}</span>
                </div>

                <div v-if="consulta?.motivo" class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Motivo:</span>
                    <span class="col-span-2 text-gray-800">{{ consulta.motivo }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Criado em:</span>
                    <span class="col-span-2 text-gray-800">{{ consulta?.created_at_formatted }}</span>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <Link :href="route('admin.clinica.consultas.show', consulta?.id || 0)">
                    <Button variant="outline" class="text-blue-600">
                        Ver Completo
                    </Button>
                </Link>
                <div class="flex gap-3">
                    <Button @click="close" class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300"> Fechar </Button>
                    <Button variant="destructive" @click="deleteConsulta(consulta!.id)"> Deletar </Button>
                </div>
            </div>
        </div>
    </div>
</template>
