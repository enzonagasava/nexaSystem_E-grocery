<script setup lang="ts">
import LoginDetails from '@/components/LoginDetails.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <div class="flex min-h-screen items-center justify-center px-4 py-12 text-black sm:px-6 lg:px-8">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <LoginDetails :canResetPassword="true" />
    </div>
</template>
