<script setup lang="ts">
import AgendamentosForm from '@/components/admin/clinica/agendamentos/AgendamentosForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { Paciente } from '@/types';

const page = usePage();
const pacientes = page.props.pacientes as Paciente[];
</script>

<template>
    <Head>
        <title>Novo Agendamento - Clínica</title>
    </Head>

    <AuthLayout>
        <AgendamentosForm :pacientes="pacientes" />
    </AuthLayout>
</template>
