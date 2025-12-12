<script setup lang="ts">
import ClientesForm from '@/components/admin/clientes/ClientesForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const cliente = page.props.cliente;
</script>

<template>
    <Head>
        <title>Editar Cliente - Família Mogi</title>
        <meta name="description" content="Gerenciamento de clientes da Família Mogi" />
    </Head>

    <AuthLayout>
        <ClientesForm :cliente="cliente" :isEditing="true" />
    </AuthLayout>
</template>
