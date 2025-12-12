<script setup lang="ts">
import PagamentoConfigForm from '@/components/admin/config/PagamentoConfigForm.vue';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const metodoPagamento = page.props.MetodoPagamento;
</script>

<template>
    <AuthLayout>
        <Head title="Configurações Gerais" />
        <div class="flex flex-col space-y-6">
            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 bg-white p-6">
                            <HeadingSmall title="Métodos de Pagamento" description="Atualize suas credenciais de pagamento" />

                            <!-- Passando o user como prop -->
                            <PagamentoConfigForm :MetodoPagamento="metodoPagamento" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
