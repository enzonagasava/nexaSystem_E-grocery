<script setup lang="ts">
import ConsultasForm from '@/components/admin/clinica/consultas/ConsultasForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { Paciente } from '@/types';

const page = usePage();
const pacientes = page.props.pacientes as Paciente[];
</script>

<template>
    <Head>
        <title>Nova Consulta - Clínica</title>
    </Head>

    <AuthLayout>
        <ConsultasForm :pacientes="pacientes" />
    </AuthLayout>
</template>
