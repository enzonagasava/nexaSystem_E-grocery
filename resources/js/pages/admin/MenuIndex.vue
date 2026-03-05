<script setup lang="ts">
import SidebarNav from '@/components/layouts/SidebarNav.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const userTipo = computed(() => String(page.props.auth?.user?.tipo_empresa ?? ''))
const isClinica = computed(() => userTipo.value === 'clinica')
const isCorretor = computed(() => userTipo.value === 'corretor')
</script>

<template>
  <div class="min-h-screen p-0 pb-24 sm:p-6">
    <Head title="Menu" />
    <h1 class="text-2xl font-semibold mb-4">Menu</h1>
    <p class="mb-4 text-sm text-muted">Todas as seções do menu rápido.</p>
    <div class="w-full bg-white dark:bg-[color:var(--sidebar-background)] p-4 rounded-none sm:rounded shadow">
      <SidebarNav :is-clinica="isClinica" :is-corretor="isCorretor" />
    </div>
  </div>
</template>

<style scoped>
.text-muted { color: var(--text-muted); }
</style>
