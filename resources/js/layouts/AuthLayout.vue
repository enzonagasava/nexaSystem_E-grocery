<script setup lang="ts">
import DropdownButtonAdmin from '@/components/ui/dropdown-button/DropdownButtonAdmin.vue';
import { Link, router } from '@inertiajs/vue3';
import { House, LogOut } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    title?: string;
}>();

const isMenuOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <div class="flex min-h-screen">
        <header class="fixed inset-x-0 top-0 z-50 flex items-center justify-between bg-gray-800 px-4 py-4 text-white lg:hidden">
            <h2 class="text-lg font-bold">Painel Admin</h2>

            <button @click="toggleMenu" class="hamburger hover:cursor-pointer" :class="{ active: isMenuOpen }">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </header>

        <transition name="slide">
            <aside v-if="isMenuOpen" class="fixed inset-y-0 left-0 z-40 w-64 space-y-4 bg-gray-800 p-4 text-white lg:hidden">
                <nav class="mt-10 space-y-2">
                    <Link :href="route('admin.dashboard')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">Dashboard</Link>
                    <Link :href="route('anuncio.config')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">Anúncios</Link>
                    <Link :href="route('paginas.config')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">Páginas</Link>
                    <Link :href="route('produtos.config')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">Produtos</Link>

                    <DropdownButtonAdmin label="Clientes" class="relative">
                        <Link :href="route('adicionar.clientes')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700"
                            >Adicionar Cliente</Link
                        >
                        <Link :href="route('clientes.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">Clientes</Link>
                    </DropdownButtonAdmin>

                    <DropdownButtonAdmin label="Pedidos" class="relative">
                        <Link :href="route('admin.pedidos.create')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700"
                            >Adicionar Pedido</Link
                        >
                        <Link :href="route('admin.pedidos.index')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">Pedidos</Link>
                    </DropdownButtonAdmin>

                    <DropdownButtonAdmin label="Atendimento" class="relative">
                        <Link :href="route('admin.chat')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">Chat</Link>
                        <Link :href="route('admin.chat.settings')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700"
                            >Configurações Gerais</Link
                        >
                    </DropdownButtonAdmin>

                    <DropdownButtonAdmin label="Configurações" class="relative">
                        <Link :href="route('config.geral')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700">Acesso</Link>
                        <Link :href="route('empresa.config.geral')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700"
                            >Informações da Empresa</Link
                        >
                        <Link :href="route('config.pagamento')" @click="closeMenu" class="block rounded px-4 py-2 hover:bg-gray-700"
                            >Métodos de Pagamento</Link
                        >
                    </DropdownButtonAdmin>

                    <Link :href="route('home')" @click="closeMenu" class="flex w-full rounded px-2 py-2 hover:bg-gray-700">
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
            </aside>
        </transition>

        <!-- DESKTOP SIDEBAR -->
        <aside class="fixed inset-y-0 left-0 hidden w-64 space-y-4 bg-gray-800 p-4 text-white lg:block">
            <h2 class="mb-6 text-xl font-bold">Painel Admin</h2>

            <nav class="space-y-2">
                <Link :href="route('admin.dashboard')" class="block rounded px-2 py-2 hover:bg-gray-700">Dashboard</Link>
                <Link :href="route('anuncio.config')" class="block rounded px-2 py-2 hover:bg-gray-700">Anúncios</Link>
                <Link :href="route('paginas.config')" class="block rounded px-2 py-2 hover:bg-gray-700">Páginas</Link>
                <Link :href="route('produtos.config')" class="block rounded px-2 py-2 hover:bg-gray-700">Produtos</Link>

                <DropdownButtonAdmin label="Clientes" class="relative">
                    <Link :href="route('adicionar.clientes')" class="block rounded px-4 py-2 hover:bg-gray-700">Adicionar Cliente</Link>
                    <Link :href="route('clientes.index')" class="block rounded px-4 py-2 hover:bg-gray-700">Clientes</Link>
                </DropdownButtonAdmin>

                <DropdownButtonAdmin label="Pedidos" class="relative">
                    <Link :href="route('admin.pedidos.create')" class="block rounded px-4 py-2 hover:bg-gray-700">Adicionar Pedido</Link>
                    <Link :href="route('admin.pedidos.index')" class="block rounded px-4 py-2 hover:bg-gray-700">Pedidos</Link>
                </DropdownButtonAdmin>

                <DropdownButtonAdmin label="Atendimento" class="relative">
                    <Link :href="route('admin.chat')" class="block rounded px-4 py-2 hover:bg-gray-700">Chat</Link>
                    <Link :href="route('admin.chat.settings')" class="block rounded px-4 py-2 hover:bg-gray-700">Configurações Gerais</Link>
                </DropdownButtonAdmin>

                <DropdownButtonAdmin label="Configurações" class="relative">
                    <Link :href="route('config.geral')" class="block rounded px-4 py-2 hover:bg-gray-700">Acesso</Link>
                    <Link :href="route('empresa.config.geral')" class="block rounded px-4 py-2 hover:bg-gray-700">Informações da Empresa</Link>
                    <Link :href="route('config.pagamento')" class="block rounded px-4 py-2 hover:bg-gray-700">Métodos de Pagamento</Link>
                </DropdownButtonAdmin>

                <Link :href="route('home')" class="flex w-full rounded px-2 py-2 hover:bg-gray-700">
                    <House class="mr-[0.5rem]" /> Ir para o site
                </Link>

                <Link class="flex w-full rounded px-2 py-2 hover:bg-gray-700" method="post" :href="route('logout')" @click="handleLogout" as="button">
                    <LogOut class="mr-[0.5rem]" /> Log out
                </Link>
            </nav>
        </aside>

        <!-- MAIN -->
        <main class="mt-16 flex-1 bg-gray-100 p-6 lg:mt-0 lg:ml-64">
            <h1 class="mb-4 text-2xl font-semibold">{{ title }}</h1>
            <slot />
        </main>
    </div>
</template>

<style scoped>
/* animação hambúrguer → X */
.hamburger {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 28px;
    height: 22px;
}

.hamburger span {
    height: 3px;
    width: 100%;
    background: white;
    border-radius: 2px;
    transition: 0.3s ease;
}

.hamburger.active span:nth-child(1) {
    transform: translateY(9.5px) rotate(45deg);
}

.hamburger.active span:nth-child(2) {
    opacity: 0;
}

.hamburger.active span:nth-child(3) {
    transform: translateY(-9.5px) rotate(-45deg);
}

/* transição lateral mobile */
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(-100%);
}
</style>
