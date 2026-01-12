<script setup lang="ts">
import ConsultasTable from '@/components/admin/clinica/consultas/ConsultasTable.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Consultas - Clínica</title>
    </Head>

    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <ConsultasTable />
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
