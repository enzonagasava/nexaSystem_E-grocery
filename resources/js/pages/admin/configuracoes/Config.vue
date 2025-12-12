<script setup lang="ts">
import ConfigForm from '@/components/admin/config/ConfigForm.vue';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import type { User } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

// Pega o usuário logado direto das props do Inertia
const page = usePage();
const user = page.props.auth.user as User;
</script>

<template>
    <AuthLayout>
        <Head title="Configurações Gerais" />
        <div class="flex flex-col space-y-6">
            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 bg-white p-6">
                            <HeadingSmall title="Configurações Gerais" description="Atualize suas informações pessoais" />

                            <!-- Passando o user como prop -->
                            <ConfigForm :user="user" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
