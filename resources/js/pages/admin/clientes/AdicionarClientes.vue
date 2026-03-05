<script setup lang="ts">
<<<<<<< HEAD
import ClientesForm from '@/components/admin/ecommerce/clientes/ClientesForm.vue';
=======
import ClientesForm from '@/components/admin/clientes/ClientesForm.vue';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
