<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
</script>

<template>
    <AuthLayout title="Cadastrar Imóvel">
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.corretor.imoveis.index')"
                    class="rounded-lg border border-gray-300 p-2 transition hover:bg-gray-50"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h2 class="text-2xl font-bold text-gray-800">Cadastrar Imóvel</h2>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <p class="text-gray-500">Formulário de cadastro em desenvolvimento...</p>
            </div>
        </div>
    </AuthLayout>
</template>
