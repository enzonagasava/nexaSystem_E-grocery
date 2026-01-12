<script setup lang="ts">
import PacientesForm from '@/components/admin/clinica/pacientes/PacientesForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Adicionar Paciente - Clínica</title>
    </Head>

    <AuthLayout>
        <PacientesForm />
    </AuthLayout>
</template>
