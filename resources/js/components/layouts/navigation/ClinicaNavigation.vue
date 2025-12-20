<script setup lang="ts">
import DropdownButtonAdmin from '@/components/ui/dropdown-button/DropdownButtonAdmin.vue';
import { Link, router } from '@inertiajs/vue3';
import { House, LogOut } from 'lucide-vue-next';

const emit = defineEmits(['close']);

const closeMenu = () => {
    emit('close');
};

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <nav class="mt-10 space-y-2 lg:mt-0">
        <Link
            :href="route('admin.clinica.dashboard')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover:bg-gray-700"
        >
            Dashboard
        </Link>

        <Link
            :href="route('admin.calendar.index')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover:bg-gray-700"
        >
            Calendário
        </Link>

        <DropdownButtonAdmin label="Pacientes" class="relative">
            <Link
                :href="route('admin.clinica.pacientes.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Adicionar Paciente
            </Link>
            <Link :href="route('admin.clinica.pacientes.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">
                Lista de Pacientes
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin label="Agendamentos" class="relative">
            <Link
                :href="route('admin.clinica.agendamentos.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Novo Agendamento
            </Link>
            <Link :href="route('admin.clinica.agendamentos.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">
                Agendamentos
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin label="Consultas" class="relative">
            <Link
                :href="route('admin.clinica.consultas.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Nova Consulta
            </Link>
            <Link :href="route('admin.clinica.consultas.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">
                Histórico de Consultas
            </Link>
        </DropdownButtonAdmin>

        <Link :href="route('admin.clinica.prontuarios.index')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">
            Prontuários
        </Link>

        <DropdownButtonAdmin label="Atendimento" class="relative">
            <Link
                :href="route('admin.chat')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Chat
            </Link>
            <Link
                :href="route('admin.chat.settings')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Configurações Gerais
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin label="Configurações" class="relative">
            <Link
                :href="route('config.geral')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Acesso
            </Link>
            <Link
                :href="route('empresa.config.geral')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Informações da Clínica
            </Link>
        </DropdownButtonAdmin>

        <Link
            :href="route('home')"
            @click="closeMenu"
            class="flex w-full rounded px-2 py-2 hover:bg-gray-700"
        >
            <House class="mr-[0.5rem]" /> Ir para o site
        </Link>

        <Link
            class="flex w-full rounded px-2 py-2 hover:bg-gray-700"
            method="post"
            :href="route('logout')"
            @click="
                handleLogout;
                closeMenu();
            "
            as="button"
        >
            <LogOut class="mr-[0.5rem]" />
            Log out
        </Link>
    </nav>
</template>
