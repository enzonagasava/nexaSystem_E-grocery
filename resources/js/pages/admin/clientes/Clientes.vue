<script setup lang="ts">
<<<<<<< HEAD
import ClientesTable from '@/components/admin/ecommerce/clientes/ClientesTable.vue';
=======
import ClientesTable from '@/components/admin/clientes/ClientesTable.vue';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Gerenciar Clientes - Família Mogi</title>
        <meta name="description" content="Gerenciamento de produtos da Família Mogi" />
    </Head>
    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
<<<<<<< HEAD
                <div class="overflow-hidden  shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200  p-6">
=======
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                        <ClientesTable />
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
