<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Inertia } from '@inertiajs/inertia';
import { defineEmits, defineProps, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    cliente: Object,
});

const emit = defineEmits(['close', 'deleted']);

const close = () => emit('close');

watch(
    () => props.show,
    (isVisible) => {
        document.body.style.overflow = isVisible ? 'hidden' : '';
    },
);

const deleteProduct = (clienteId: number) => {
    if (confirm(`Tem certeza de que deseja excluir o Cliente ID: ${clienteId}?`)) {
        alert(`Excluindo o Cliente ID: ${clienteId}`);
        Inertia.delete(`/clientes/deletar-cliente/${clienteId}`, {
            onSuccess: () => {
                alert(`Cliente ID: ${clienteId} excluído com sucesso!`);
                emit('deleted', clienteId);
            },
            onError: () => {
                alert(`Erro ao excluir o Cliente ID: ${clienteId}.`);
            },
        });
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-xs" @click.self="close">
        <div class="relative w-full max-w-lg rounded-lg border bg-white p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3)]">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" @click="close">✕</button>

            <h2 class="mb-4 text-xl font-bold">Detalhes do Cliente</h2>

            <div class="space-y-3">
                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Nome:</span>
                    <span class="col-span-2 text-gray-800">{{ cliente?.nome }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Número:</span>
                    <span class="col-span-2 text-gray-800">{{ cliente?.numero }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">E-mail:</span>
                    <span class="col-span-2 text-gray-800">{{ cliente?.email || '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Endereço:</span>
                    <span class="col-span-2 text-gray-800">{{ cliente?.endereco_completo || '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">CEP:</span>
                    <span class="col-span-2 text-gray-800">{{ cliente?.cep || '—' }}</span>
                </div>

                <div class="grid grid-cols-3">
                    <span class="font-semibold text-gray-700">Data de Criação:</span>
                    <span class="col-span-2 text-gray-800">{{ cliente?.created_at_formatted }}</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <Button @click="close" class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300"> Fechar </Button>
                <Button variant="destructive" @click="deleteProduct(cliente.id)"> Deletar </Button>
            </div>
        </div>
    </div>
</template>
