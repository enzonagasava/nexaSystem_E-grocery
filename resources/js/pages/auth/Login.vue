<script setup lang="ts">
import LoginDetails from '@/components/LoginDetails.vue';
import { Head } from '@inertiajs/vue3';
<<<<<<< HEAD
import ThemeToggle from '@/components/ui/theme/ThemeToggle.vue'
=======
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <div class="flex min-h-screen items-center justify-center px-4 py-12 text-primary sm:px-6 lg:px-8">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <LoginDetails :canResetPassword="true" />
<<<<<<< HEAD
        <div class="pt-2">
      <ThemeToggle />
=======
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    </div>
    </div>
    
</template>
