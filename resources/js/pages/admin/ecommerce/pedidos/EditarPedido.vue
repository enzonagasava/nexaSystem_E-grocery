<script setup lang="ts">
import PedidosForm from '@/components/admin/ecommerce/pedidos/adicionarPedidos/PedidosForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    plataformas?: Array<{ id: number; nome: string }>;
    pedidoEditavel?: unknown;
}>();
</script>

<template>
    <Head>
        <title>Editar Pedido</title>
        <meta name="description" content="Editar pedido" />
    </Head>

    <AuthLayout>
        <PedidosForm :plataformas="props.plataformas ?? []" :pedido-editavel="props.pedidoEditavel" />
    </AuthLayout>
</template>
