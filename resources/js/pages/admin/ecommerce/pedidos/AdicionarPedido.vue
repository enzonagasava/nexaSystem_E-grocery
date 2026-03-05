<script setup lang="ts">
import PedidosForm from '@/components/admin/ecommerce/pedidos/adicionarPedidos/PedidosForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    plataformas?: Array<{ id: number; nome: string }>;
}>();
</script>

<template>
    <Head>
        <title>Adicionar Pedido</title>
        <meta name="description" content="Adicionar novo pedido" />
    </Head>

    <AuthLayout>
        <PedidosForm :plataformas="props.plataformas ?? []" />
    </AuthLayout>
</template>
