<script setup lang="ts">
<<<<<<< HEAD
import NexaLoadingOverlay from '@/components/NexaLoadingOverlay.vue';
import { router, usePage } from '@inertiajs/vue3';
import MobileBottomNav from '@/components/ui/MobileBottomNav.vue'
import { ref, watch, nextTick, onBeforeUnmount } from 'vue';
import SidebarNav from '@/components/layouts/SidebarNav.vue'
=======
import DropdownButtonAdmin from '@/components/ui/dropdown-button/DropdownButtonAdmin.vue';
import ClinicaNavigation from '@/components/layouts/navigation/ClinicaNavigation.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { House, LogOut } from 'lucide-vue-next';
import { computed, ref } from 'vue';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

const props = defineProps<{
    title?: string;
    auth?: {
        user?: string;
    };
    hideGlobalLoading?: boolean;
}>();

<<<<<<< HEAD
const page = usePage<any>();
=======
const page = usePage();
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
const isMenuOpen = ref(false);


const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const hamburgerBtn = ref<HTMLElement | null>(null);
const sidebarRef = ref<HTMLElement | null>(null);

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') closeMenu();
}

watch(isMenuOpen, async (open) => {
    if (open) {
        document.body.classList.add('overflow-hidden');
        await nextTick();
        const firstFocusable = sidebarRef.value?.querySelector('a,button');
        (firstFocusable as HTMLElement | null)?.focus?.();
        document.addEventListener('keydown', handleKeydown);
    } else {
        document.body.classList.remove('overflow-hidden');
        document.removeEventListener('keydown', handleKeydown);
        hamburgerBtn.value?.focus?.();
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.classList.remove('overflow-hidden');
});

const handleLogout = () => {
    router.flushAll();
};

<<<<<<< HEAD
const userTipo = String((page.props.auth && page.props.auth.user && page.props.auth.user.empresa_id) ?? '');

const modulo = String(page.props.modulo ?? '');
console.log(modulo)
const titleDashboard = String(page.props.empresaTipo?.nome ?? '');
</script>

<template>
    <div class="flex min-h-screen app-layout">
        <!-- Global Nexa loading overlay (can be hidden per-page) -->
        <NexaLoadingOverlay v-if="!props.hideGlobalLoading" />
        <header class="fixed inset-x-0 top-0 z-50 flex items-center justify-between  px-4 py-4 text-inverse lg:hidden" style="background-color: var(--sidebar-background);">
            <h2 class="font-bold">{{ titleDashboard }}</h2>
        </header>

        <transition name="slide">
            <div v-if="isMenuOpen">
                <div class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="closeMenu" aria-hidden="true"></div>
                <aside id="mobile-sidebar" ref="sidebarRef" class="fixed inset-y-0 left-0 z-40 w-64 space-y-4  p-4 text-inverse lg:hidden" role="dialog" aria-modal="true">
                    <SidebarNav :modulo="modulo" @close="closeMenu" />
                </aside>
            </div>
        </transition>

        <!-- Mobile bottom navigation -->
        <MobileBottomNav class="lg:hidden" :modulo="modulo" @openSidebar="toggleMenu" />

        <aside 
            class="sidebar hidden lg:block" 
            style="background-color: var(--sidebar-background);"
        >
            <div class="p-4">
                <h2 class="mb-6 font-bold">{{ titleDashboard }}</h2>
                <SidebarNav :modulo="modulo" @close="closeMenu" />
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- Header móvel (se necessário) -->
            <div class="lg:hidden p-4 border-b">
                <!-- Menu hamburger aqui -->
            </div>

            <!-- Conteúdo -->
            <div class="flex flex-col flex-1 min-h-0 p-6">
                <h1 class="mb-4 font-semibold">{{ title }}</h1>
                <slot />
            </div>
=======
// Verifica se o usuário é de uma clínica
const isClinica = computed(() => page.props.auth?.user?.tipo_empresa === 'clinica');

const painelTitle = computed(() => {
    return isClinica.value ? 'Painel Clínica' : 'Painel Admin';
});
</script>

<template>
    <div class="flex min-h-screen">
        <header class="fixed inset-x-0 top-0 z-50 flex items-center justify-between bg-gray-800 px-4 py-4 text-white lg:hidden">
            <h2 class="text-lg font-bold">{{ painelTitle }}</h2>

            <button @click="toggleMenu" class="hamburger hover:cursor-pointer" :class="{ active: isMenuOpen }">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </header>

        <transition name="slide">
            <aside v-if="isMenuOpen" class="fixed inset-y-0 left-0 z-40 w-64 space-y-4 bg-gray-800 p-4 text-white lg:hidden">
                <!-- Navegação Clínica -->
                <ClinicaNavigation v-if="isClinica" @close="closeMenu" />

                <!-- Navegação Padrão (E-commerce/Admin) -->
                <nav v-else class="mt-10 space-y-2">
                    <Link :href="route('admin.dashboard')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">Dashboard</Link>
                    <Link :href="route('admin.calendar.index')" @click="closeMenu" class="block rounded px-2 py-2 hover:bg-gray-700">Calendário</Link>
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
            <h2 class="mb-6 text-xl font-bold">{{ painelTitle }}</h2>

            <!-- Navegação Clínica -->
            <ClinicaNavigation v-if="isClinica" @close="closeMenu" />

            <!-- Navegação Padrão (E-commerce/Admin) -->
            <nav v-else class="space-y-2">
                <Link :href="route('admin.dashboard')" class="block rounded px-2 py-2 hover:bg-gray-700">Dashboard</Link>
                <Link :href="route('admin.calendar.index')" class="block rounded px-2 py-2 hover:bg-gray-700">Calendário</Link>
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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

.app-layout {
    display: flex;
    min-height: 100vh;
    width: 100%;
    overflow: hidden; /* Previne scroll desnecessário */
}

.sidebar {
    width: 16rem;
    flex-shrink: 0; /* Impede que o sidebar encolha */
    height: 100vh;
    position: sticky;
    top: 0;
    overflow-y: auto; /* Scroll próprio se necessário */
}

/* Fix rápido: forçar cor de texto da sidebar para o token inverse */
.sidebar, .sidebar * {
    color: var(--text-inverse) !important;
}

.main-content {
    flex: 1;
    min-width: 0; /* Importante para flexbox */
    width: 100%;
    overflow-x: auto;
    overflow-y: auto;
    height: 100vh;
}

/* Para conteúdos que precisam de largura total */
.main-content > * {
    max-width: 100%;
}

/* Ajuste para tabelas ou grids largos */
.main-content :deep(table),
.main-content :deep(.table-responsive) {
    width: 100%;
    overflow-x: auto;
    display: block;
}

/* Responsivo */
@media (max-width: 1023px) {
    .app-layout {
        flex-direction: column;
    }
    
    .sidebar {
        display: none; /* Esconde sidebar em mobile */
    }
    
    .main-content {
        height: auto;
        min-height: 100vh;
    }
}
</style>