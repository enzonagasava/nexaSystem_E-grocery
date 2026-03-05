<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Blog - Configurações</title>
        <meta name="description" content="Configurações do blog" />
    </Head>
    <AuthLayout>
        <div class="flex min-h-screen justify-center px-4 py-12 text-black sm:px-6 lg:px-8">
            <div class="w-[1366px]">
                <h1>Configurar Blog</h1>
                <p>Bem-vindo ao painel!</p>
            </div>
        </div>
    </AuthLayout>
</template>
