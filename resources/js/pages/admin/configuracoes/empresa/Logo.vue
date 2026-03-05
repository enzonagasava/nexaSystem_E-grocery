<script setup lang="ts">
import LogoForm from '@/components/admin/config/empresa/LogoEmpresaForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import LayoutEmpresa from '@/layouts/config/LayoutEmpresa.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const logo = (page.props as any).logo;
</script>

<template>
    <AuthLayout>
        <Head title="Informações da Empresa" />
        <div class="flex flex-col space-y-6">
            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
<<<<<<< HEAD
                    <div class="overflow-hidden  shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200  p-6">
=======
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 bg-white p-6">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                            <LayoutEmpresa>
                                <LogoForm :initialLogo="logo" />
                            </LayoutEmpresa>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
