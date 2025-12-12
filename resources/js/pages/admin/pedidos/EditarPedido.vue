<script setup lang="ts">
import PedidosForm from '@/components/admin/pedidos/adicionarPedidos/PedidosForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    plataformas: Array,
    pedidoEditavel: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <Head>
        <title>Editar Pedido - Família Mogi</title>
        <meta name="description" content="Gerenciamento de clientes da Família Mogi" />
    </Head>

    <AuthLayout>
        <PedidosForm :plataformas="props.plataformas" :pedidoEditavel="pedidoEditavel" />
    </AuthLayout>
</template>
