<script setup lang="ts">
import { Button, ButtonTable } from '@/components/ui/button';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { Trash } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import PedidoProdutoModal from './PedidoProdutoModal.vue';
const props = defineProps({ modelValue: Array });

const emit = defineEmits(['update:modelValue', 'update:valorTotal']);

const showModal = ref(false);

function adicionarProduto(produto) {
    emit('update:modelValue', [...props.modelValue, produto]);
    showModal.value = false;
}

function removerItem(index: number) {
    const item = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', item);
}

const valorTotal = computed(() => {
    return props.modelValue.reduce((acc, p) => {
        const preco = Number(p.valor) || 0;
        const qtd = Number(p.quantidade) || 1;
        return acc + preco * qtd;
    }, 0);
});

watch(valorTotal, (novoValor) => {
    emit('update:valorTotal', novoValor);
});
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <HeadingSmall title="Adicionar Pedido" />
            <Button @click="showModal = true"> Adicionar Produto </Button>
        </div>

        <table class="w-full border text-left">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2">Produto</th>
                    <th class="p-2">Quantidade</th>
                    <th class="p-2">Valor</th>
                    <th class="p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(p, i) in props.modelValue" :key="i">
                    <td class="p-2">{{ p.nome }}</td>
                    <td class="p-2">{{ p.quantidade }}</td>
                    <td class="p-2">R$ {{ p.valor }}</td>
                    <td class="p-2">
                        <ButtonTable @click="removerItem(i)" :icon="Trash" label="Remover" class="text-red-600 hover:text-red-900" />
                    </td>
                </tr>
            </tbody>
        </table>

        <PedidoProdutoModal v-if="showModal" @close="showModal = false" @adicionar="adicionarProduto" />
    </div>
</template>
