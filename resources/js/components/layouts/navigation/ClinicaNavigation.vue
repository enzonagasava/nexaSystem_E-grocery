<script setup lang="ts">
<<<<<<< HEAD
import type { Auth } from '@/types'
import DropdownButtonAdmin from '@/components/ui/dropdown-button/DropdownButtonAdmin.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { House, LogOut } from 'lucide-vue-next'
import ThemeToggle from '@/components/ui/theme/ThemeToggle.vue'
import { podeVer } from '@/utils/permissao'

const emit = defineEmits(['close'])
const page = usePage()
const auth = (page.props.auth as Auth) ?? null

const closeMenu = () => {
    emit('close')
}

const handleLogout = () => {
    router.flushAll()
}
</script>

<template>
    <nav class="space-y-2 lg:mt-0">
        <Link
            :href="route('admin.clinica.dashboard')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover-bg-primary"
=======
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        >
            Dashboard
        </Link>

        <Link
<<<<<<< HEAD
            v-if="podeVer('agenda.visualizar', auth)"
            :href="route('admin.clinica.calendar.index')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover-bg-primary"
=======
            :href="route('admin.calendar.index')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover:bg-gray-700"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        >
            Calendário
        </Link>

<<<<<<< HEAD
        <DropdownButtonAdmin v-if="podeVer('pacientes.visualizar', auth)" label="Pacientes" class="relative">
            <Link
                :href="route('admin.clinica.pacientes.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Adicionar Paciente
            </Link>
            <Link :href="route('admin.clinica.pacientes.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
=======
        <DropdownButtonAdmin label="Pacientes" class="relative">
            <Link
                :href="route('admin.clinica.pacientes.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Adicionar Paciente
            </Link>
            <Link :href="route('admin.clinica.pacientes.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                Lista de Pacientes
            </Link>
        </DropdownButtonAdmin>

<<<<<<< HEAD
        <DropdownButtonAdmin v-if="podeVer('agenda.visualizar', auth)" label="Agendamentos" class="relative">
            <Link
                :href="route('admin.clinica.agendamentos.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Novo Agendamento
            </Link>
            <Link :href="route('admin.clinica.agendamentos.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
=======
        <DropdownButtonAdmin label="Agendamentos" class="relative">
            <Link
                :href="route('admin.clinica.agendamentos.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Novo Agendamento
            </Link>
            <Link :href="route('admin.clinica.agendamentos.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                Agendamentos
            </Link>
        </DropdownButtonAdmin>

<<<<<<< HEAD
        <DropdownButtonAdmin v-if="podeVer('agenda.visualizar', auth)" label="Consultas" class="relative">
            <Link
                :href="route('admin.clinica.consultas.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Nova Consulta
            </Link>
            <Link :href="route('admin.clinica.consultas.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
=======
        <DropdownButtonAdmin label="Consultas" class="relative">
            <Link
                :href="route('admin.clinica.consultas.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
            >
                Nova Consulta
            </Link>
            <Link :href="route('admin.clinica.consultas.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                Histórico de Consultas
            </Link>
        </DropdownButtonAdmin>

<<<<<<< HEAD
        <Link v-if="podeVer('pacientes.visualizar', auth)" :href="route('admin.clinica.prontuarios.index')" @click="closeMenu" class="block rounded px-2 py-2 hover-bg-primary">
            Prontuários
        </Link>

        <DropdownButtonAdmin v-if="podeVer('chat.visualizar', auth)" label="Atendimento" class="relative">
            <Link
                :href="route('admin.clinica.chat')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
=======
        <Link :href="route('admin.clinica.prontuarios.index')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">
            Prontuários
        </Link>

        <DropdownButtonAdmin label="Atendimento" class="relative">
            <Link
                :href="route('admin.chat')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            >
                Chat
            </Link>
            <Link
<<<<<<< HEAD
                :href="route('admin.clinica.chat.settings')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
=======
                :href="route('admin.chat.settings')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover:bg-gray-700"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            >
                Configurações Gerais
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin label="Configurações" class="relative">
            <Link
                :href="route('config.geral')"
                @click="closeMenu"
<<<<<<< HEAD
                class="block rounded px-4 py-2 hover-bg-primary"
=======
                class="block rounded px-4 py-2 hover:bg-gray-700"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            >
                Acesso
            </Link>
            <Link
                :href="route('empresa.config.geral')"
                @click="closeMenu"
<<<<<<< HEAD
                class="block rounded px-4 py-2 hover-bg-primary"
=======
                class="block rounded px-4 py-2 hover:bg-gray-700"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            >
                Informações da Clínica
            </Link>
        </DropdownButtonAdmin>

        <Link
            :href="route('home')"
            @click="closeMenu"
<<<<<<< HEAD
            class="flex w-full rounded px-2 py-2 hover-bg-primary"
=======
            class="flex w-full rounded px-2 py-2 hover:bg-gray-700"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        >
            <House class="mr-[0.5rem]" /> Ir para o site
        </Link>

        <Link
<<<<<<< HEAD
            class="flex w-full rounded px-2 py-2 hover-bg-primary"
=======
            class="flex w-full rounded px-2 py-2 hover:bg-gray-700"
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
<<<<<<< HEAD
        <div class="mt-4 px-2">
            <ThemeToggle />
        </div>
=======
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    </nav>
</template>
