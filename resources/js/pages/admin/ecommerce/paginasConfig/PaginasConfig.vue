<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Gerenciar Páginas</title>
        <meta name="description" content="Gerenciamento de páginas" />
    </Head>
    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <h1 class="mb-6 text-2xl font-bold text-gray-800">Gerenciar Páginas</h1>
                        <p class="text-gray-600">Configuração de páginas do site.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
