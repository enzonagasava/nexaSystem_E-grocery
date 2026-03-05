<script setup lang="ts">
import ClientesForm from '@/components/admin/ecommerce/clientes/ClientesForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const cliente = (page.props as { cliente?: unknown }).cliente;
</script>

<template>
    <Head>
        <title>Editar Cliente</title>
        <meta name="description" content="Editar cliente" />
    </Head>

    <AuthLayout>
        <ClientesForm :cliente="cliente" :is-editing="true" />
    </AuthLayout>
</template>
