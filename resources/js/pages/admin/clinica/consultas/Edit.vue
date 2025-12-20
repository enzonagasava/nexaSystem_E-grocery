<script setup lang="ts">
import ConsultasForm from '@/components/admin/clinica/consultas/ConsultasForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { Consulta, Paciente } from '@/types';

const page = usePage();
const consulta = page.props.consulta as Consulta;
const pacientes = page.props.pacientes as Paciente[];
</script>

<template>
    <Head>
        <title>Editar Consulta - Clínica</title>
    </Head>

    <AuthLayout>
        <ConsultasForm :consulta="consulta" :pacientes="pacientes" :isEditing="true" />
    </AuthLayout>
</template>
