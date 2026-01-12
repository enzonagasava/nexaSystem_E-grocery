<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Home,
    Building2,
    Users,
    Phone,
    DollarSign,
    Mail,
    BarChart3,
    CalendarDays,
    MessageSquare,
    Settings,
    LogOut,
} from 'lucide-vue-next';

defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();

// Verifica se a rota atual está ativa
const isActive = (routeName: string) => {
    return page.url.includes(routeName);
};
</script>

<template>
    <nav class="mt-10 space-y-2 lg:mt-0">
        <Link
            :href="route('admin.corretor.dashboard')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/corretor/dashboard') }"
        >
            <Home class="mr-2 h-5 w-5" />
            Dashboard
        </Link>

        <Link
            :href="route('admin.corretor.imoveis.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/corretor/imoveis') }"
        >
            <Building2 class="mr-2 h-5 w-5" />
            Imóveis
        </Link>

        <Link
            :href="route('admin.corretor.leads.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/corretor/leads') }"
        >
            <Users class="mr-2 h-5 w-5" />
            Leads
        </Link>

        <Link
            :href="route('admin.corretor.contatos.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/corretor/contatos') }"
        >
            <Phone class="mr-2 h-5 w-5" />
            Contatos
        </Link>

        <Link
            :href="route('admin.corretor.financeiro.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/corretor/financeiro') }"
        >
            <DollarSign class="mr-2 h-5 w-5" />
            Crédito & Financiamentos
        </Link>

        <Link
            :href="route('admin.corretor.emails.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/corretor/emails') }"
        >
            <Mail class="mr-2 h-5 w-5" />
            Email
        </Link>

        <Link
            :href="route('admin.corretor.relatorios.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/corretor/relatorios') }"
        >
            <BarChart3 class="mr-2 h-5 w-5" />
            Relatórios
        </Link>

        <div class="my-2 border-t border-gray-700"></div>

        <Link
            :href="route('admin.calendar.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/calendar') }"
        >
            <CalendarDays class="mr-2 h-5 w-5" />
            Calendário
        </Link>

        <Link
            :href="route('admin.chat')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/chat') }"
        >
            <MessageSquare class="mr-2 h-5 w-5" />
            Chat
        </Link>

        <Link
            :href="route('clientes.index')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/clientes') }"
        >
            <Users class="mr-2 h-5 w-5" />
            Clientes
        </Link>

        <Link
            :href="route('admin.chat.settings')"
            @click="$emit('close')"
            class="flex items-center rounded px-2 py-2 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive('/admin/chat/settings') }"
        >
            <Settings class="mr-2 h-5 w-5" />
            Configurações
        </Link>

        <Link
            class="flex w-full items-center rounded px-2 py-2 hover:bg-gray-700"
            method="post"
            :href="route('logout')"
            @click="$emit('close')"
            as="button"
        >
            <LogOut class="mr-2 h-5 w-5" />
            Sair
        </Link>
    </nav>
</template>
