<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    prontuarios: Array<any>;
}>();
</script>

<template>
    <Head>
        <title>Prontuários - Clínica</title>
    </Head>

    <AuthLayout title="Prontuários">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <p class="text-gray-600 dark:text-gray-400">
                Lista de prontuários será exibida aqui.
            </p>
        </div>
    </AuthLayout>
</template>
