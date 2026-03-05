<script setup lang="ts">
import SidebarNav from '@/components/layouts/SidebarNav.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const userTipo = computed(() => String((page.props.auth as { user?: { tipo_empresa?: string } })?.user?.tipo_empresa ?? ''));
const isClinica = computed(() => userTipo.value === 'clinica');
const isCorretor = computed(() => userTipo.value === 'corretor');
</script>

<template>
    <AuthLayout>
        <div class="min-h-screen p-0 pb-24 sm:p-6">
            <Head title="Menu" />
            <h1 class="mb-4 text-2xl font-semibold">Menu</h1>
            <p class="mb-4 text-sm text-muted">Todas as seções do menu rápido.</p>
            <div class="w-full rounded-none bg-white p-4 shadow sm:rounded dark:bg-[color:var(--sidebar-background)]">
                <SidebarNav :is-clinica="isClinica" :is-corretor="isCorretor" />
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.text-muted {
    color: var(--text-muted);
}
</style>
