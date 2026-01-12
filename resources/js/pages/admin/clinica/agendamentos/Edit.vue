<script setup lang="ts">
import AgendamentosForm from '@/components/admin/clinica/agendamentos/AgendamentosForm.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { Agendamento, Paciente } from '@/types';

const page = usePage();
const agendamento = page.props.agendamento as Agendamento;
const pacientes = page.props.pacientes as Paciente[];
</script>

<template>
    <Head>
        <title>Editar Agendamento - Clínica</title>
    </Head>

    <AuthLayout>
        <AgendamentosForm :agendamento="agendamento" :pacientes="pacientes" :isEditing="true" />
    </AuthLayout>
</template>
