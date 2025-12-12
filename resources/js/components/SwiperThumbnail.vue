<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { Navigation, Pagination, Thumbs } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { ref } from 'vue';
import ButtonAddCart from './ui/button/ButtonAddCart.vue';
import ButtonPortion from './ui/button/ButtonPortion.vue';

const activeModules = [Navigation, Thumbs, Pagination];

const props = defineProps<{
    title?: string;
}>();
const page = usePage();

const produtos = page.props.produtoSwiper || [];

console.log(produtos);

const selectedPortions = ref<Record<number | string, string>>({});

const selectPortion = (slideId: number, portion: string) => {
    selectedPortions.value[slideId] = portion;
};
</script>

<template>
    <div class="relative flex w-full flex-col items-center rounded-lg bg-[#f5f5f5] px-2 py-6">
        <div class="relative w-full max-w-[1366px]">
            <h2 class="mb-4 w-full text-[24px] font-bold lg:text-[36px]">{{ props.title }}</h2>

            <Swiper
                :modules="activeModules"
                :slides-per-view="4"
                :navigation="{
                    prevEl: '.custom-swiper-prev',
                    nextEl: '.custom-swiper-next',
                }"
                :loop="true"
                :space-between="20"
                class="my-custom-swiper"
                :breakpoints="{
                    0: { slidesPerView: 1, spaceBetween: 30 },
                    640: { slidesPerView: 2, spaceBetween: 30 },
                    768: { slidesPerView: 3, spaceBetween: 30 },
                    1024: { slidesPerView: 4, spaceBetween: 58 },
                }"
            >
                <SwiperSlide v-for="produto in produtos" :key="produto.id">
                    <div class="flex flex-col items-center rounded-lg bg-white p-4 shadow-md">
                        <img
                            :src="produto.imageUrl"
                            :title="produto.nome"
                            alt="Produto"
                            class="mb-2 h-[100%] w-[300px] cursor-pointer rounded-[8px] object-cover hover:brightness-[1.10]"
                            @click="$inertia.visit(route('anuncio', produto.id))"
                        />
                        <div class="mt-2 mb-1 text-lg font-semibold">
                            <h4 class="cursor-pointer" @click="$inertia.visit(route('anuncio', produto.id))">{{ produto.nome }}</h4>
                        </div>
                        <div class="mb-2 text-xs text-gray-600">selecione a porção</div>
                        <div class="mb-3 flex gap-2">
                            <ButtonPortion :produto="produto" :selectedPortions="selectedPortions" @select-portion="selectPortion" />
                        </div>
                        <ButtonAddCart
                            :produto="produto"
                            :portion="selectedPortions[produto.id]"
                            title="Adicionar no Carrinho"
                            @add-to-cart="cartItemCount++"
                        />
                    </div>
                </SwiperSlide>

                <!-- Navegação personalizada -->
                <template #container-end>
                    <button class="custom-swiper-prev nav-btn" aria-label="Anterior"><i class="fa-solid fa-angle-left"></i></button>
                    <button class="custom-swiper-next nav-btn" aria-label="Próximo"><i class="fa-solid fa-angle-right"></i></button>
                </template>
            </Swiper>
        </div>
    </div>
</template>

<style scoped>
.my-custom-swiper {
    width: 100%;
    height: 370px;
    padding: 0 3rem !important;
    position: relative;
}
.swiper-slide {
    display: flex;
    justify-content: center;
    align-items: stretch;
    position: relative;
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
