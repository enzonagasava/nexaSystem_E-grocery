<script setup lang="ts">
import ClientesForm from '@/components/admin/clientes/ClientesForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Adicionar Cliente - Família Mogi</title>
        <meta name="description" content="Gerenciamento de clientes da Família Mogi" />
    </Head>

    <AuthLayout>
        <ClientesForm />
    </AuthLayout>
</template>
