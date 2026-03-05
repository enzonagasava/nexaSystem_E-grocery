<script setup lang="ts">
<<<<<<< HEAD
import ClientesForm from '@/components/admin/ecommerce/clientes/ClientesForm.vue';
=======
import ClientesForm from '@/components/admin/clientes/ClientesForm.vue';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
<<<<<<< HEAD
const cliente = (page.props as any).cliente;
=======
const cliente = page.props.cliente;
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
