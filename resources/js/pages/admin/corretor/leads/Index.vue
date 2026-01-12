<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps<{
    leads: any[];
}>();
</script>

<template>
    <AuthLayout title="Leads">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Leads</h2>
                <Link
                    :href="route('admin.corretor.leads.create')"
                    class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white transition hover:bg-green-700"
                >
                    <Plus class="h-4 w-4" />
                    Novo Lead
                </Link>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center justify-center py-12 text-gray-400">
                    <p>Nenhum lead cadastrado ainda.</p>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
