<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { Home, Calendar, ShoppingCart, List, Clock, Users, MapPin, MessageSquare } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{ modulo: string }>()

const page = usePage()
const current = computed(() => page.url || page.props?.url || '')

const dashboardHref = computed(() => {
  if (props.modulo === 'clinica') return route('admin.clinica.dashboard')
  if (props.modulo === 'corretor') return route('admin.corretor.dashboard')
  return route('admin.ecommerce.dashboard')
})

const calendarHref = computed(() => {
  if (props.modulo === 'clinica') return route('admin.clinica.calendar.index')
  if (props.modulo === 'corretor') return route('admin.corretor.calendar.index')
  return route('admin.ecommerce.calendar.index')
})

const menuHref = computed(() => {
  if (props.modulo === 'clinica') return route('admin.clinica.menu.index')
  if (props.modulo === 'corretor') return route('admin.corretor.menu.index')
  return route('admin.ecommerce.menu.index')
})

</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white/90 dark:bg-[color:var(--sidebar-background)] border-t border-gray-200 dark:border-gray-800 backdrop-blur-md">
    <div class="max-w-5xl mx-auto flex justify-between items-center px-4 py-2">
      <!-- Clínica -->
      <template v-if="page.props.modulo === 'clinica'">
        <Link :href="dashboardHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Dashboard">
          <Home class="w-5 h-5" />
          <span class="text-xs">Home</span>
        </Link>

        <Link :href="calendarHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Calendário">
          <Calendar class="w-5 h-5" />
          <span class="text-xs">Calendário</span>
        </Link>

        <Link :href="route('admin.clinica.agendamentos.index')" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Agendamentos">
          <Clock class="w-5 h-5" />
          <span class="text-xs">Agendamentos</span>
        </Link>

        <Link :href="menuHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Menu">
          <List class="w-5 h-5" />
          <span class="text-xs">Menu</span>
        </Link>
      </template>

      <!-- Corretor -->
      <template v-else-if="page.props.modulo === 'corretor'">
        <Link :href="dashboardHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Dashboard">
          <Home class="w-5 h-5" />
          <span class="text-xs">Home</span>
        </Link>

        <Link :href="calendarHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Calendário">
          <Calendar class="w-5 h-5" />
          <span class="text-xs">Calendário</span>
        </Link>

        <Link :href="route('admin.corretor.imoveis.index')" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Imóveis">
          <MapPin class="w-5 h-5" />
          <span class="text-xs">Imóveis</span>
        </Link>

        <Link :href="menuHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Menu">
          <List class="w-5 h-5" />
          <span class="text-xs">Menu</span>
        </Link>
      </template>

      <!-- Fallback (admin) -->
      <template v-else>
        <Link :href="dashboardHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Home">
          <Home class="w-5 h-5" />
          <span class="text-xs">Home</span>
        </Link>

        <Link :href="calendarHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Calendário">
          <Calendar class="w-5 h-5" />
          <span class="text-xs">Calendário</span>
        </Link>

        <Link :href="route('admin.ecommerce.pedidos.index')" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Pedidos">
          <ShoppingCart class="w-5 h-5" />
          <span class="text-xs">Pedidos</span>
        </Link>

        <Link :href="menuHref" class="flex-1 flex flex-col items-center justify-center py-2 text-sm hover-bg-primary text-primary" aria-label="Menu">
          <List class="w-5 h-5" />
          <span class="text-xs">Menu</span>
        </Link>
      </template>
    </div>
  </nav>
</template>

<style scoped>
nav { box-shadow: 0 -1px 10px rgba(0,0,0,0.04);} 
</style>
