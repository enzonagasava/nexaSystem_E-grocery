<script setup lang="ts">
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
        >
            Dashboard
        </Link>

        <Link
            v-if="podeVer('agenda.visualizar', auth)"
            :href="route('admin.clinica.calendar.index')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover-bg-primary"
        >
            Calendário
        </Link>

        <DropdownButtonAdmin v-if="podeVer('pacientes.visualizar', auth)" label="Pacientes" class="relative">
            <Link
                :href="route('admin.clinica.pacientes.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Adicionar Paciente
            </Link>
            <Link :href="route('admin.clinica.pacientes.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
                Lista de Pacientes
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin v-if="podeVer('agenda.visualizar', auth)" label="Agendamentos" class="relative">
            <Link
                :href="route('admin.clinica.agendamentos.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Novo Agendamento
            </Link>
            <Link :href="route('admin.clinica.agendamentos.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
                Agendamentos
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin v-if="podeVer('agenda.visualizar', auth)" label="Consultas" class="relative">
            <Link
                :href="route('admin.clinica.consultas.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Nova Consulta
            </Link>
            <Link :href="route('admin.clinica.consultas.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
                Histórico de Consultas
            </Link>
        </DropdownButtonAdmin>

        <Link v-if="podeVer('pacientes.visualizar', auth)" :href="route('admin.clinica.prontuarios.index')" @click="closeMenu" class="block rounded px-2 py-2 hover-bg-primary">
            Prontuários
        </Link>

        <DropdownButtonAdmin v-if="podeVer('chat.visualizar', auth)" label="Atendimento" class="relative">
            <Link
                :href="route('admin.clinica.chat')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Chat
            </Link>
            <Link
                :href="route('admin.clinica.chat.settings')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Configurações Gerais
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin label="Configurações" class="relative">
            <Link
                :href="route('config.geral')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Acesso
            </Link>
            <Link
                :href="route('empresa.config.geral')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Informações da Clínica
            </Link>
        </DropdownButtonAdmin>

        <Link
            :href="route('home')"
            @click="closeMenu"
            class="flex w-full rounded px-2 py-2 hover-bg-primary"
        >
            <House class="mr-[0.5rem]" /> Ir para o site
        </Link>

        <Link
            class="flex w-full rounded px-2 py-2 hover-bg-primary"
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
        <div class="mt-4 px-2">
            <ThemeToggle />
        </div>
    </nav>
</template>
