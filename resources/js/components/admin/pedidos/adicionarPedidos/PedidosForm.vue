<script setup lang="ts">
import { onMounted, reactive, watch } from 'vue';
import PedidoClienteForm from './PedidoClienteForm.vue';
import PedidoProdutos from './PedidoProdutos.vue';
import PedidoResumo from './PedidoResumo.vue';

const props = defineProps({
    plataformas: Array,
    pedidoEditavel: {
        type: Object,
        default: null,
    },
});

const pedido = reactive({
    id: null,
    cod_pedido: null,
    clienteSelecionado: null,
    endereco: null,
    plataformaSelecionada: null,
    data: new Date().toISOString().slice(0, 16),
    status: 'Em Andamento',
    produtos: [],
    valorTotal: 0,
});

onMounted(() => {
    if (props.pedidoEditavel) {
        pedido.id = props.pedidoEditavel.id || null;
        pedido.cod_pedido = props.pedidoEditavel.cod_pedido || null;
        pedido.clienteSelecionado = props.pedidoEditavel.cliente || null;
        pedido.data = props.pedidoEditavel.data || new Date().toISOString().slice(0, 16);
        pedido.status = props.pedidoEditavel.status || 'Em Andamento';
        pedido.endereco = props.pedidoEditavel.endereco || null;
        pedido.plataformaSelecionada = props.pedidoEditavel.plataforma_id || null;

        pedido.produtos =
            props.pedidoEditavel.cod_pedidos?.map((p) => ({
                id: p.produto_id,
                nome: p.produto?.nome || '—',
                quantidade: p.quantidade,
                valor: p.valor_pedido,
            })) || [];

        pedido.valorTotal = pedido.produtos.reduce((acc, p) => acc + p.valor * p.quantidade, 0);
    }
});

watch(
    () => pedido.produtos,
    (novo) => {
        pedido.valorTotal = novo.reduce((acc, p) => acc + p.valor * p.quantidade, 0);
    },
    { deep: true },
);
</script>

<template>
    <div class="space-y-6 rounded-2xl bg-white p-6 shadow">
        <div class="flex items-baseline justify-between">
            <h1 class="text-2xl font-bold">
                {{ props.pedidoEditavel ? 'Editar Pedido' : 'Adicionar Pedido' }}
            </h1>
            <div v-if="props.pedidoEditavel">
                <span class="font-semibold">#{{ props.pedidoEditavel.cod_pedido }}</span>
            </div>
        </div>

        <PedidoClienteForm
            v-model="pedido.clienteSelecionado"
            v-model:status="pedido.status"
            v-model:data="pedido.data"
            v-model:endereco="pedido.endereco"
            v-model:plataformaSelecionada="pedido.plataformaSelecionada"
            :plataformas="props.plataformas"
        />

        <PedidoProdutos v-model="pedido.produtos" v-model:valorTotal="pedido.valorTotal" :pedidoEditavel="props.pedidoEditavel" />

        <PedidoResumo :pedido="pedido" />
    </div>
</template>
