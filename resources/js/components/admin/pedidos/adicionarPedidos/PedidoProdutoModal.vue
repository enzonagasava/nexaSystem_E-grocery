<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Produto, ProdutoSelecionado } from '@/types/index';
import axios from 'axios';
import { ref, watch } from 'vue';

const emit = defineEmits(['close', 'adicionar']);

const produtos = ref<Produto[]>([]);
const search = ref('');
const produtoSelecionado = ref<ProdutoSelecionado | null>(null);
let controller: AbortController | null = null;

async function buscarProdutos() {
    const termo = search.value.trim();

    if (termo.length === 0) {
        produtos.value = [];
        produtoSelecionado.value = null;
        return;
    }

    if (controller) controller.abort();
    controller = new AbortController();

    try {
        console.log('acessando rota');
        const { data } = await axios.get<Produto[]>('/admin/pedidos/buscarProduto', {
            params: { search: termo },
            signal: controller.signal,
        });
        produtos.value = data;
    } catch (err: any) {
        if (err.name === 'CanceledError') return;
    }
}

const close = () => emit('close');

function selecionarProduto(item: Produto) {
    if (produtoSelecionado.value && produtoSelecionado.value.id === item.id) {
        produtoSelecionado.value.quantidade += 1;
    } else {
        const precoBase = item.tamanhos?.[0]?.pivot?.preco ?? 0;

        produtoSelecionado.value = {
            ...item,
            quantidade: 1,
            valor_unitario: precoBase,
            valor: precoBase,
        };
    }
}

watch(
    () => [produtoSelecionado.value?.quantidade, produtoSelecionado.value?.valor_unitario],
    ([quantidade, valorUnitario]) => {
        if (!produtoSelecionado.value || !quantidade || !valorUnitario) return;
        produtoSelecionado.value.valor = quantidade * valorUnitario;
    },
);

function adicionar() {
    if (!produtoSelecionado.value) return;
    emit('adicionar', { ...produtoSelecionado.value });
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-xs">
        <div class="relative w-96 space-y-4 rounded-lg bg-white p-6">
            <h3 class="text-lg font-semibold">Adicionar Produto</h3>

            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" @click="close">✕</button>

            <!-- Campo de busca -->
            <input
                v-model="search"
                placeholder="Pesquisar Produto"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500"
                @input="buscarProdutos"
            />

            <!-- Lista de produtos -->
            <div class="max-h-60 space-y-2 overflow-y-auto">
                <div
                    v-for="item in produtos"
                    :key="item.id"
                    class="flex cursor-pointer items-center gap-4 rounded-xl border p-3 transition hover:shadow-md"
                    @click="selecionarProduto(item)"
                >
                    <img :src="item.imagens?.[0]?.imagem_url" alt="Imagem do produto" class="h-16 w-16 rounded-lg border object-cover" />
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold">{{ item.titulo }}</h3>
                        <p class="line-clamp-2 text-xs text-gray-600">{{ item.descricao }}</p>
                        <div class="mt-1 flex justify-between text-xs">
                            <span class="font-medium text-blue-600"> R$ {{ item.tamanhos?.[0]?.pivot?.preco ?? '—' }} </span>
                            <span class="text-gray-500"> {{ item.estoque }} em estoque </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produto selecionado -->
            <div v-if="produtoSelecionado" class="space-y-2 border-t pt-3">
                <label class="block font-medium">Produto Selecionado:</label>
                <p class="font-semibold text-gray-700">{{ produtoSelecionado.titulo }}</p>

                <div class="flex gap-2">
                    <input
                        v-model.number="produtoSelecionado.quantidade"
                        type="number"
                        min="1"
                        :max="produtoSelecionado.estoque"
                        placeholder="Quantidade"
                        class="w-1/2 rounded-lg border border-gray-300 px-3 py-2"
                    />
                    <input
                        :value="produtoSelecionado ? produtoSelecionado.valor.toFixed(2) : '0.00'"
                        type="text"
                        step="0.01"
                        placeholder="Valor"
                        class="w-1/2 rounded-lg border border-gray-300 bg-gray-100 px-3 py-2"
                        readonly
                    />
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-2 pt-2">
                <Button @click="emit('close')" variant="destructive"> Cancelar </Button>
                <Button @click="adicionar" variant="default" :disabled="!produtoSelecionado"> Adicionar </Button>
            </div>
        </div>
    </div>
</template>
