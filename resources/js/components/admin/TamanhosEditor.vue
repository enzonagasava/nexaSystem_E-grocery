<script setup lang="ts">
import { useMoneyConfig } from '@/composables/useMoneyConfig';
import { defineEmits, defineProps } from 'vue';

const moneyConfig = useMoneyConfig();
type Tamanho = {
    nome: string;
    preco: string;
};

defineProps<{
    tamanhos: Tamanho[];
}>();

const emits = defineEmits<{
    (e: 'adicionar'): void;
    (e: 'remover', index: number): void;
}>();

function onRemover(index: number) {
    emits('remover', index);
}
</script>

<template>
    <div>
        <label class="mb-2 block font-semibold text-gray-700">Tamanhos e Preços</label>
        <div v-for="(tamanho, index) in tamanhos" :key="index" class="mb-3 flex items-center gap-4">
            <input
                v-model="tamanho.nome"
                type="text"
                placeholder="Ex: 200g, 1kg"
                class="flex-1 rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                required
            />
            <input
                v-model.lazy="tamanho.preco"
                v-money3="moneyConfig"
                placeholder="Preço"
                class="w-32 rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                required
            />
            <button
                type="button"
                @click="onRemover(index)"
                class="rounded border border-red-600 px-2 py-1 font-bold text-red-600 hover:text-red-800"
                title="Remover tamanho"
            >
                &times;
            </button>
        </div>
        <button
            type="button"
            @click="$emit('adicionar')"
            class="mt-2 rounded bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
        >
            + Adicionar Tamanho
        </button>
    </div>
</template>
