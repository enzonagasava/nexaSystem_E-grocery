<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps<{
    emails: any[];
}>();
</script>

<template>
    <AuthLayout title="Email Marketing">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Campanhas de Email</h2>
                <Link
                    :href="route('admin.corretor.emails.create')"
                    class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-700"
                >
                    <Plus class="h-4 w-4" />
                    Nova Campanha
                </Link>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center justify-center py-12 text-gray-400">
                    <p>Nenhuma campanha criada ainda.</p>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
