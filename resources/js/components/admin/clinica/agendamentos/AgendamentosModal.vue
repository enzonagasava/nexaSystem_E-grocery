<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { AgendamentoListItem } from '@/types';

const props = defineProps<{
    show: boolean;
    agendamento: AgendamentoListItem | null;
}>();

const emit = defineEmits(['close', 'deleted']);

const close = () => emit('close');

watch(
    () => props.show,
    (isVisible) => {
        document.body.style.overflow = isVisible ? 'hidden' : '';
    },
);

const deleteAgendamento = (agendamentoId: number) => {
    if (confirm(`Tem certeza de que deseja excluir o agendamento ID: ${agendamentoId}?`)) {
        router.delete(route('admin.clinica.agendamentos.destroy', agendamentoId), {
            onSuccess: () => {
                emit('deleted', agendamentoId);
                close();
            },
            onError: () => {
                alert(`Erro ao excluir o agendamento.`);
            },
        });
    }
};

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
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-xs" @click.self="close">
        <div class="relative w-full max-w-lg rounded-lg border  p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3)]">
            <Button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" @click="close">✕</Button>

            <h2 class="mb-4 text-xl font-bold">Detalhes do Agendamento</h2>

            <div class="space-y-3">
                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Paciente:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento?.paciente_nome }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Telefone:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento?.paciente_telefone }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Data:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento?.data_formatada }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Hora:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento?.hora_formatada }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Duração:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento?.duracao_minutos }} minutos</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Tipo:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento?.tipo }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Status:</span>
                    <span class="col-span-2">
                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClass(agendamento?.status || '')]">
                            {{ getStatusLabel(agendamento?.status || '') }}
                        </span>
                    </span>
                </div>

                <div v-if="agendamento?.observacoes" class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Observações:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento.observacoes }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Criado em:</span>
                    <span class="col-span-2 text-gray-800">{{ agendamento?.created_at_formatted }}</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <Button @click="close" class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300"> Fechar </Button>
                <Button variant="destructive" @click="deleteAgendamento(agendamento!.id)"> Deletar </Button>
            </div>
        </div>
    </div>
</template>
