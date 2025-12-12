<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { Navigation, Pagination, Thumbs } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { computed, ref } from 'vue';
import ButtonAddCart from './ui/button/ButtonAddCart.vue';

const activeModules = [Navigation, Thumbs, Pagination];

const page = usePage();

const produto = ref(page.props.produto || {});

const imagens = produto.value.imagens;

const selectedWeight = ref(produto.value.tamanhos && produto.value.tamanhos.length > 0 ? produto.value.tamanhos[0].nome : '');

const imagem_paths = imagens.map((element) => element.imagem_path);
const productStock = produto.value.estoque || 0;
const quantidadeSelecionada = ref(1);

const precoSelecionado = computed(() => {
    if (!produto.value || !Array.isArray(produto.value.tamanhos)) return 0;
    const tamanho = produto.value.tamanhos.find((t) => t.nome === selectedWeight.value);
    return tamanho && tamanho.pivot ? parseFloat(tamanho.pivot.preco) : 0;
});

const calculateShipping = () => {
    alert('Calcular frete!');
    // Lógica para calcular frete
};

const toggleFavorite = () => {
    alert('Togglou favorito!');
    // Lógica para favoritar/desfavoritar
};

const toggleShare = () => {
    alert('Togglou Share!');
    // Lógica para favoritar/desfavoritar
};
</script>

<template>
    <div class="flex justify-center bg-gray-100">
        <div class="flex min-h-screen flex-col justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex w-full max-w-7xl flex-col overflow-hidden rounded-lg bg-white shadow-xl md:flex-row">
                <div class="flex w-full flex-col items-center p-6 md:w-1/2">
                    <div class="flex h-full w-full">
                        <div class="mr-4 flex flex-col space-y-2">
                            <button
                                v-for="(thumb, index) in thumbnails"
                                :key="index"
                                @click="selectImage(thumb)"
                                class="h-20 w-20 flex-shrink-0 cursor-pointer overflow-hidden rounded-md border-2"
                                :class="{ 'border-green-500': thumb === mainImage, 'border-gray-300': thumb !== mainImage }"
                            >
                                <img :src="thumb" alt="Miniatura" class="h-full w-full object-cover" />
                            </button>
                        </div>
                        <Swiper
                            :modules="activeModules"
                            :slides-per-view="1"
                            :navigation="{
                                prevEl: '.custom-swiper-prev',
                                nextEl: '.custom-swiper-next',
                            }"
                            :loop="true"
                            :space-between="20"
                            class="my-custom-swiper"
                        >
                            <SwiperSlide v-for="(img, index) in imagem_paths" :key="index">
                                <div class="flex h-full items-center justify-center overflow-hidden rounded-lg border border-gray-200">
                                    <img :src="`/storage/${img}`" :alt="productName" class="max-h-[500px] max-w-full object-contain" />
                                </div>
                            </SwiperSlide>
                            <template #container-end>
                                <button class="custom-swiper-prev nav-btn" aria-label="Anterior"><i class="fa-solid fa-angle-left"></i></button>
                                <button class="custom-swiper-next nav-btn" aria-label="Próximo"><i class="fa-solid fa-angle-right"></i></button>
                            </template>
                        </Swiper>
                    </div>
                </div>

                <div class="flex w-full flex-col justify-between p-6 md:w-1/2">
                    <div class="mb-4 flex items-center justify-between text-sm text-gray-600">
                        <div>Home > Cogumelos > <span class="font-semibold text-gray-800">Shitake</span></div>
                        <div class="flex">
                            <button @click="toggleFavorite" class="flex cursor-pointer items-center text-[#6aab9c] hover:text-[#6aab9c]">
                                <svg class="mr-1 h-5 w-5 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                                    />
                                </svg>
                                Comparar /
                            </button>
                            <button @click="toggleShare" class="cursor-pointer text-[#6aab9c]">Compartilhar</button>
                        </div>
                    </div>

                    <h1 class="mb-2 text-3xl font-bold text-gray-900">{{ produto.nome }}</h1>

                    <div class="mb-4 text-2xl font-bold text-gray-900">
                        R${{ precoSelecionado.toFixed(2) }}
                        <p class="mt-1 cursor-pointer text-sm font-normal text-black hover:underline">Ver formas de pagamento</p>
                    </div>

                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-bold text-gray-700">Selecione a porção</label>
                        <div class="flex space-x-2">
                            <button
                                v-for="tamanho in produto.tamanhos"
                                :key="tamanho.nome"
                                @click="selectedWeight = tamanho.nome"
                                :class="{
                                    'bg-[#6aab9c] text-white': selectedWeight === tamanho.nome,
                                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-50': selectedWeight !== tamanho.nome,
                                }"
                                class="cursor-pointer rounded-md border-2 px-4 py-2 text-sm font-medium"
                            >
                                {{ tamanho.nome }}
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2 text-sm font-bold text-gray-700">Estoque disponível</p>
                        <div class="flex items-center">
                            <label class="mr-2 text-gray-900">Quantidade: </label>
                            <select v-model="quantidadeSelecionada" name="quantidade" id="quantidade">
                                <option v-for="n in Math.min(productStock)" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6 flex flex-col space-y-3">
                        <ButtonAddCart
                            :produto="produto"
                            :portion="selectedWeight"
                            :quantidade="quantidadeSelecionada"
                            title="Comprar Agora"
                            :redirectToCart="true"
                            @add-to-cart="cartItemCount++"
                        />

                        <ButtonAddCart
                            :produto="produto"
                            :portion="selectedWeight"
                            :quantidade="quantidadeSelecionada"
                            title="Adicionar no Carrinho"
                            @add-to-cart="cartItemCount++"
                        />
                    </div>

                    <div class="mb-6">
                        <p class="mb-2 text-sm font-bold text-gray-700">Calcular o frete:</p>
                        <div class="flex items-center">
                            <input
                                type="text"
                                placeholder="Insira o seu CEP"
                                class="mr-2 flex-grow rounded-md border border-gray-300 px-3 py-2 text-gray-700 focus:border-transparent focus:ring-2 focus:ring-[#6aab9c] focus:outline-none"
                            />
                            <button
                                @click="calculateShipping"
                                class="rounded-md bg-gray-200 px-4 py-2 font-semibold text-gray-700 transition duration-300 hover:bg-gray-300"
                            >
                                Calcular
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 max-w-7xl rounded-lg bg-white px-4 py-8 shadow-xl sm:px-6 lg:px-8">
                <h2 class="mb-4 text-2xl font-bold text-gray-900">Descrição:</h2>
                <div class="prose max-w-none text-gray-700" v-html="produto.descricao"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos se necessário, mas o Tailwind já faz a maior parte */
.prose ul {
    list-style-type: disc;
    margin-left: 1.25em;
}
.prose li {
    margin-bottom: 0.5em;
}

/* Botões de navegação customizados */
.nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.85);
    border: none;
    border-radius: 50%;
    width: 3rem;
    height: 3rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    z-index: 10;
    font-size: 1.5rem;
    color: #48bb78;
}

.nav-btn > i {
    font-size: 2rem;
}

.nav-btn:hover {
    background: #48bb78;
    color: white;
}

.custom-swiper-prev {
    left: 0.5rem;
}

.custom-swiper-next {
    right: 0.5rem;
}

@media (max-width: 768px) {
    .my-custom-swiper {
        height: auto;
        padding: 0 1rem !important;
    }
    .nav-btn {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1.2rem;
    }
    .custom-swiper-prev {
        left: 0.25rem;
    }
    .custom-swiper-next {
        right: 0.25rem;
    }
}
</style>
