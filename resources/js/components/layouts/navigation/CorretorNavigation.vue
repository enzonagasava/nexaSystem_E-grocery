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
console.log(auth)

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
            :href="route('admin.corretor.dashboard')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover-bg-primary"
        >
            Dashboard
        </Link>

        <Link
            v-if="podeVer('agenda.visualizar', auth)"
            :href="route('admin.corretor.calendar.index')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover-bg-primary"
        >
            Calendário
        </Link>

        <DropdownButtonAdmin v-if="podeVer('imoveis.visualizar', auth)" label="Anúncios" class="relative">
            <Link
                :href="route('admin.corretor.listings.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Novo Anúncio
            </Link>
            <Link :href="route('admin.corretor.listings.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
                Meus Anúncios
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin v-if="podeVer('leads.visualizar', auth)" label="Kanban" class="relative">
            <Link
                :href="route('admin.corretor.kanban.index')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Oportunidades
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin v-if="podeVer('leads.visualizar', auth)" label="Leads" class="relative">
            <Link
                :href="route('admin.corretor.leads.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Adicionar Lead
            </Link>
            <Link :href="route('admin.corretor.leads.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
                Meus Leads
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin label="Contatos" class="relative">
            <Link
                :href="route('admin.corretor.contatos.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Adicionar Contato
            </Link>
            <Link :href="route('admin.corretor.contatos.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
                Meus Contatos
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin v-if="podeVer('imoveis.visualizar', auth)" label="Imóveis" class="relative">
            <Link
                :href="route('admin.corretor.imoveis.create')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Adicionar Imóvel
            </Link>
            <Link :href="route('admin.corretor.imoveis.index')" @click="closeMenu" class="block rounded px-4 py-2 hover-bg-primary">
                Meus Imóveis
            </Link>
        </DropdownButtonAdmin>

        <Link
            :href="route('admin.corretor.relatorios.index')"
            @click="closeMenu"
            class="block rounded px-2 py-2 hover-bg-primary"
        >
            Relatórios
        </Link>

        <DropdownButtonAdmin v-if="podeVer('chat.visualizar', auth)" label="Atendimento" class="relative">
            <Link
                :href="route('admin.corretor.chat')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Chat
            </Link>
            <Link
                :href="route('admin.corretor.chat.settings')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Configurações Gerais
            </Link>
        </DropdownButtonAdmin>

        <DropdownButtonAdmin label="Configurações" class="relative">
            <Link
                :href="route('admin.corretor.config.geral')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Acesso
            </Link>
            <Link
                :href="route('admin.corretor.empresa.config.geral')"
                @click="closeMenu"
                class="block rounded px-4 py-2 hover-bg-primary"
            >
                Informações da Empresa
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
