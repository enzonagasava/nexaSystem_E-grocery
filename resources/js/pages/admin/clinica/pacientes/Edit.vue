<script setup lang="ts">
import PacientesForm from '@/components/admin/clinica/pacientes/PacientesForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { Paciente } from '@/types';

const page = usePage();
const paciente = page.props.paciente as Paciente;
</script>

<template>
    <Head>
        <title>Editar Paciente - Clínica</title>
    </Head>

    <AuthLayout>
        <PacientesForm :paciente="paciente" :isEditing="true" />
    </AuthLayout>
</template>
