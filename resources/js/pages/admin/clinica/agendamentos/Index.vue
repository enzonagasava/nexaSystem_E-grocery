<script setup lang="ts">
import AgendamentosTable from '@/components/admin/clinica/agendamentos/AgendamentosTable.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Agendamentos - Clínica</title>
    </Head>

    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden  shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200  p-6">
                        <AgendamentosTable />
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
