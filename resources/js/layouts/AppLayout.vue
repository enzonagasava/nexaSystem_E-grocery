<script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

// --- INICIALIZAÇÃO DE STORES E ESTADO ---

const page = usePage();
const cartStore = useCartStore();

// Watch e Inicialização do Carrinho
watch(
    () => page.props.cartItems,
    (newItems) => {
        cartStore.setCart(newItems || []);
    },
    { immediate: true },
); // Usando 'immediate: true' para inicializar na montagem

// Refs e Estado
const currentYear = ref(0);
const isMenuOpen = ref(false);
const dropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null); // Ref para o 'click outside'

// Computed para estado de Login
const userLogado = computed(() => !!page.props.auth?.user).value;
const logoutForm = useForm({});

// --- FUNÇÕES DE CONTROLE DE MENU/DROPDOWN ---

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
    closeDropdown(); // Garante que o dropdown feche ao abrir o menu mobile
};

const logout = () => {
    logoutForm.post('/logout', {
        onFinish: () => Inertia.visit('/'),
    });
};

// Funções combinadas para uso nos links do dropdown
const closeMenuAndDropdown = () => {
    closeMenu();
    closeDropdown();
};

const logoutAndCloseMenu = () => {
    logout();
    closeDropdown();
};

// --- LÓGICA DE CLICK OUTSIDE (Não pode ser simplificada muito mais) ---

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownOpen.value && dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        closeDropdown();
    }
};

onMounted(() => {
    currentYear.value = new Date().getFullYear();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="/css/app.css" />
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <header class="fixed top-0 right-0 left-0 z-50 bg-white shadow-md">
        <div class="flex h-[70px] w-screen justify-center px-4 sm:px-6 lg:px-8">
            <div class="flex w-full max-w-[1366px] items-center justify-between">
                <div class="flex w-full items-center">
                    <img src="/images/logo-sem-fundo.png" class="h-[50px] cursor-pointer" alt="familiaMogi-logo" @click="$inertia.visit('/')" />
                    <div
                        class="w-full justify-between"
                        :class="{
                            'hidden md:flex': !isMenuOpen,
                            'absolute top-full left-0 z-40 flex w-full flex-col items-center bg-white py-4 text-center shadow-lg md:hidden':
                                isMenuOpen, // mostra no mobile com estilo
                        }"
                    >
                        <nav class="ml-4 flex flex-col gap-2 md:flex-row md:gap-4">
                            <Link :href="route('produtos.index')" @click="closeMenu" class="rounded px-4 py-2 hover:bg-gray-100">Produtos</Link>
                            <Link href="/about-us" @click="closeMenu" class="rounded px-4 py-2 hover:bg-gray-100">Quem Somos</Link>
                            <Link href="/how-to-buy" @click="closeMenu" class="rounded px-4 py-2 hover:bg-gray-100">Como Comprar</Link>
                            <Link href="/contact" @click="closeMenu" class="rounded px-4 py-2 hover:bg-gray-100">Contato</Link>
                            <Link href="#" @click="closeMenu" class="rounded px-4 py-2 hover:bg-gray-100">Área do Produtor</Link>
                        </nav>
                        <div class="flex items-center">
                            <div v-if="userLogado" class="relative inline-block" ref="dropdownRef">
                                <button
                                    @click="toggleDropdown"
                                    class="flex items-center rounded bg-[#6aab9c] px-4 py-2 text-white transition hover:cursor-pointer hover:bg-[#77bdad] focus:ring-2 focus:ring-[#6aab9c] focus:ring-offset-2 focus:outline-none"
                                >
                                    <span>{{ page.props.auth.user.name || 'Usuário' }}</span>
                                    <svg
                                        :class="{ 'rotate-180': dropdownOpen }"
                                        class="ml-2 h-4 w-4 fill-current transition-transform duration-200"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M5.516 7.548a.75.75 0 011.06 0L10 10.97l3.424-3.423a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 010-1.06z"
                                        />
                                    </svg>
                                </button>

                                <div
                                    :class="{ 'visible opacity-100': dropdownOpen, 'invisible opacity-0': !dropdownOpen }"
                                    class="absolute right-0 w-36 origin-top-right rounded-md bg-white shadow-lg transition-opacity duration-200"
                                >
                                    <Link
                                        :href="page.props.auth.user.cargo_id === 1 ? '/admin/dashboard' : '/cliente/dashboard'"
                                        @click="closeMenuAndDropdown"
                                        class="block rounded-t-md px-4 py-2 text-gray-800 hover:bg-gray-100"
                                    >
                                        Dashboard
                                    </Link>
                                    <button
                                        @click="logoutAndCloseMenu"
                                        class="w-full rounded-b-md px-4 py-2 text-left text-gray-800 hover:bg-gray-100"
                                    >
                                        Logout
                                    </button>
                                </div>
                            </div>

                            <Link
                                v-else
                                href="/login"
                                @click="closeMenu"
                                class="rounded bg-[#6aab9c] px-4 py-2 text-white transition hover:bg-[#77bdad]"
                            >
                                Login
                            </Link>
                            <Link
                                href="/carrinho"
                                @click="closeMenu"
                                class="ml-2 rounded bg-[#6aab9c] px-4 py-2 text-white transition hover:bg-[#77bdad]"
                            >
                                Carrinho ({{ cartStore.cartQuantity }})
                            </Link>
                        </div>
                    </div>
                </div>

                <button @click="toggleMenu" class="p-2 hover:cursor-pointer focus:outline-none md:hidden">
                    <div :class="['hamburger', { active: isMenuOpen }]">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>
            </div>
        </div>
    </header>

    <main class="min-h-screen pt-[70px]"><slot /></main>

    <footer class="mt-8 w-full bg-[#d3b381] px-4 py-8">
        <div class="mx-auto flex max-w-[1366px] flex-col justify-between gap-8 md:flex-row">
            <div>
                <h3 class="font-bold">Contatos</h3>
                <ul class="leading-[2]">
                    <li>
                        <Link href="#">Fale Conosco</Link>
                    </li>
                    <li class="flex items-center">
                        <i class="fa-brands fa-whatsapp mr-2 flex" style="color: #fff; font-size: 25px"></i>
                        <Link href="#">1199999-9999</Link>
                    </li>
                    <li class="flex items-center">
                        <i class="fa-brands fa-instagram mr-2 flex" style="color: #fff; font-size: 25px"></i>
                        <Link href="#">@familiaMogi</Link>
                    </li>
                    <li class="flex items-center">
                        <i class="fa-solid fa-location-dot mr-2" style="color: #fff; font-size: 25px"></i>
                        <Link href="#">Estrada Missao Kinoshita</Link>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="mb-1 font-bold">Categorias</h3>
                <ul>
                    <li><Link href="#">COGUMELOS FRESCOS</Link></li>
                    <li><Link href="#">COMBOS</Link></li>
                    <li><Link href="#">CONGELADOS DIVERSOS</Link></li>
                    <li><Link href="#">EMPÓRIO</Link></li>
                    <li><Link href="#">ORGÂNICOS</Link></li>
                    <li><Link href="#">PROMO</Link></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-1 font-bold">Ferramentas</h3>
                <ul>
                    <li>
                        <Link href="#">Seja um Parceiro</Link>
                    </li>
                    <li>
                        <Link href="#">Área de Entrega</Link>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="mb-1 font-bold">Termos</h3>
                <ul>
                    <li>
                        <Link href="#">Política de Privacidade</Link>
                    </li>
                    <li>
                        <Link href="#">Trocas e Devoluções</Link>
                    </li>
                </ul>
            </div>
        </div>
        <div class="mt-8 text-center text-sm">Família Mogi - CNPJ: ©Todos os direitos reservados. {{ currentYear }}</div>
    </footer>

    <a href="https://wa.me/5511999999999" target="_blank" class="fixed right-10 bottom-10 z-50 lg:right-15 lg:bottom-15">
        <img src="/images/whatsapp.png" alt="WhatsApp" class="h-16 w-16 rounded-full shadow-lg transition-shadow duration-300 hover:shadow-xl" />
    </a>
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
    background: black;
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
