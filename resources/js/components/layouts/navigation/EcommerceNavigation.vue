<script setup lang="ts">
import type { Auth } from '@/types'
import DropdownButtonAdmin from '@/components/ui/dropdown-button/DropdownButtonAdmin.vue'
import Button from '@/components/ui/button/Button.vue'
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

function logout() {
  router.flushAll()
  router.post(route('logout'))
  emit('close')
}
</script>

<template>
  <nav class="space-y-2 lg:mt-0">
    <Link
      :href="route('admin.ecommerce.dashboard')"
      @click="closeMenu"
      class="block rounded px-2 py-2 hover-bg-primary"
    >
      Dashboard
    </Link>

    <Link
      v-if="podeVer('agenda.visualizar', auth)"
      :href="route('admin.ecommerce.calendar.index')"
      @click="closeMenu"
      class="block rounded px-2 py-2 hover-bg-primary"
    >
      Calendário
    </Link>

    <Link :href="route('admin.ecommerce.anuncio.config')" @click="closeMenu" class="block rounded px-2 py-2 hover-bg-primary">
      Anúncios
    </Link>
    <Link :href="route('admin.ecommerce.paginas.config')" @click="closeMenu" class="block rounded px-2 py-2 hover-bg-primary">
      Páginas
    </Link>
    <Link :href="route('admin.ecommerce.produtos.config')" @click="closeMenu" class="block rounded px-2 py-2 hover-bg-primary">
      Produtos
    </Link>

    <DropdownButtonAdmin label="Clientes" class="relative">
      <Link :href="route('admin.ecommerce.adicionar.clientes')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Adicionar Cliente
      </Link>
      <Link :href="route('admin.ecommerce.clientes.index')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Clientes
      </Link>
    </DropdownButtonAdmin>

    <DropdownButtonAdmin label="Pedidos" class="relative">
      <Link :href="route('admin.ecommerce.pedidos.create')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Adicionar Pedido
      </Link>
      <Link :href="route('admin.ecommerce.pedidos.index')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Pedidos
      </Link>
    </DropdownButtonAdmin>

    <DropdownButtonAdmin v-if="podeVer('chat.visualizar', auth)" label="Atendimento" class="relative">
      <Link :href="route('admin.ecommerce.chat')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Chat
      </Link>
      <Link :href="route('admin.ecommerce.chat.settings')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Configurações Gerais
      </Link>
    </DropdownButtonAdmin>

    <DropdownButtonAdmin label="Configurações" class="relative">
      <Link :href="route('admin.ecommerce.config.geral')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Acesso
      </Link>
      <Link :href="route('admin.ecommerce.empresa.config.geral')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Informações da Empresa
      </Link>
      <Link :href="route('admin.ecommerce.config.pagamento')" class="block rounded px-4 py-2 hover-bg-primary" @click="closeMenu">
        Métodos de Pagamento
      </Link>
    </DropdownButtonAdmin>

    <Link :href="route('home')" @click="closeMenu" class="flex w-full rounded px-2 py-2 hover-bg-primary">
      <House class="mr-[0.5rem]" /> Ir para o site
    </Link>

    <Button class="flex w-full rounded px-2 py-2 hover-bg-primary" @click="logout">
      <LogOut class="mr-[0.5rem]" /> Log out
    </Button>
    <div class="mt-4 px-2">
      <ThemeToggle />
    </div>
  </nav>
</template>
