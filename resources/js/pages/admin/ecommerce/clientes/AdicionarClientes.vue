<script setup lang="ts">
import ClientesForm from '@/components/admin/ecommerce/clientes/ClientesForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Adicionar Cliente</title>
        <meta name="description" content="Adicionar novo cliente" />
    </Head>

    <AuthLayout>
        <ClientesForm />
    </AuthLayout>
</template>
