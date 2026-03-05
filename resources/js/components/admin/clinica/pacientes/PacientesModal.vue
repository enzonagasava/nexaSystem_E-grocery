<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { Paciente } from '@/types';

const props = defineProps<{
    show: boolean;
    paciente: Paciente | null;
}>();

const emit = defineEmits(['close', 'deleted']);

const close = () => emit('close');

watch(
    () => props.show,
    (isVisible) => {
        document.body.style.overflow = isVisible ? 'hidden' : '';
    },
);

const deletePaciente = (pacienteId: number) => {
    if (confirm(`Tem certeza de que deseja excluir o paciente ID: ${pacienteId}?`)) {
        router.delete(route('admin.clinica.pacientes.destroy', pacienteId), {
            onSuccess: () => {
                emit('deleted', pacienteId);
                close();
            },
            onError: () => {
                alert(`Erro ao excluir o paciente.`);
            },
        });
    }
};
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

            <h2 class="mb-4 text-xl font-bold">Detalhes do Paciente</h2>

            <div class="space-y-3">
                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Nome:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.nome }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">CPF:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.cpf || '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Telefone:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.telefone }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">E-mail:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.email || '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Idade:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.idade ? `${paciente.idade} anos` : '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Endereço:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.endereco_completo || '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Convênio:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.convenio || '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Nº Convênio:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.numero_convenio || '—' }}</span>
                </div>

                <div v-if="paciente?.observacoes" class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Observações:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente.observacoes }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Cadastrado em:</span>
                    <span class="col-span-2 text-gray-800">{{ paciente?.created_at_formatted }}</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <Button @click="close" class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300"> Fechar </Button>
                <Button variant="destructive" @click="deletePaciente(paciente!.id!)"> Deletar </Button>
            </div>
        </div>
    </div>
</template>
