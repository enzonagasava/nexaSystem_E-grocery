<script setup lang="ts">
import NexaLoadingOverlay from '@/components/NexaLoadingOverlay.vue';
import { router, usePage } from '@inertiajs/vue3';
import MobileBottomNav from '@/components/ui/MobileBottomNav.vue'
import { ref, watch, nextTick, onBeforeUnmount } from 'vue';
import SidebarNav from '@/components/layouts/SidebarNav.vue'

const props = defineProps<{
    title?: string;
    auth?: {
        user?: string;
    };
    hideGlobalLoading?: boolean;
}>();

const page = usePage<any>();
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