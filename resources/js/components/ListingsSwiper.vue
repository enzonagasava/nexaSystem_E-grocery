<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { Navigation, Pagination, Thumbs } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { MapPin } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface ListingItem {
  id: number;
  anuncio_ativo: boolean;
  anuncio_status?: string | null;
  created_at: string;
  imovel?: {
    id: number;
    nome?: string;
    codigo?: string;
    categoria?: string;
    cidade?: string;
    bairro?: string;
    valor_venda?: number | string | null;
    valor_locacao?: number | string | null;
    imageUrl?: string | null;
  } | null;
}

const activeModules = [Navigation, Thumbs, Pagination];

const props = defineProps<{
    title?: string;
    listings?: ListingItem[];
}>();

const page = usePage();
const anuncios: ListingItem[] = props.listings || (page.props.listingsSwiper as ListingItem[]) || [];

function formatCurrency(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return '—';
  const num = typeof value === 'string' ? parseFloat(value.replace(/,/g, '.')) : value;
  if (isNaN(Number(num))) return '—';
  return Number(num).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
</script>

<template>
    <div class="relative flex w-full flex-col items-center rounded-lg bg-[#f5f5f5] px-2 py-6 dark:bg-gray-800">
        <div class="relative w-full max-w-[1366px]">
            <h2 class="mb-4 w-full text-[24px] font-bold lg:text-[36px] dark:text-white">{{ props.title || 'Anúncios em Destaque' }}</h2>

            <Swiper
                :modules="activeModules"
                :slides-per-view="4"
                :navigation="{
                    prevEl: '.custom-swiper-prev-listings',
                    nextEl: '.custom-swiper-next-listings',
                }"
                :loop="true"
                :space-between="20"
                class="my-custom-swiper-listings"
                :breakpoints="{
                    0: { slidesPerView: 1, spaceBetween: 30 },
                    640: { slidesPerView: 2, spaceBetween: 30 },
                    768: { slidesPerView: 3, spaceBetween: 30 },
                    1024: { slidesPerView: 4, spaceBetween: 20 },
                }"
            >
                <SwiperSlide v-for="listing in anuncios" :key="listing.id">
                    <div class="flex flex-col rounded-lg bg-white shadow-md hover:shadow-lg transition-shadow dark:bg-gray-900 overflow-hidden h-full">
                        <!-- Imagem -->
                        <div 
                            class="relative h-48 w-full bg-muted flex items-center justify-center overflow-hidden cursor-pointer group"
                            @click="$inertia.visit(route('admin.corretor.listings.show', listing.id))"
                        >
                            <img
                                v-if="listing.imovel?.imageUrl"
                                :src="listing.imovel.imageUrl"
                                :alt="listing.imovel?.nome || 'Imóvel'"
                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="text-muted-foreground text-sm">Sem imagem</div>
                            
                            <!-- Badge de status -->
                            <div class="absolute top-2 right-2">
                                <span 
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="listing.anuncio_ativo ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                                >
                                    {{ listing.anuncio_ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>

                        <!-- Conteúdo -->
                        <div class="flex flex-col flex-1 p-4">
                            <h4 
                                class="text-base font-semibold truncate cursor-pointer hover:text-primary transition-colors mb-2"
                                @click="$inertia.visit(route('admin.corretor.listings.show', listing.id))"
                            >
                                {{ listing.imovel?.nome || 'Anúncio #' + listing.id }}
                            </h4>
                            
                            <p v-if="listing.imovel?.codigo" class="text-xs text-muted-foreground mb-1">
                                Código: {{ listing.imovel.codigo }}
                            </p>
                            
                            <p v-if="listing.imovel?.cidade || listing.imovel?.bairro" class="text-sm text-muted-foreground flex items-center gap-1 mb-3">
                                <MapPin class="w-3 h-3 flex-shrink-0" />
                                <span class="truncate">{{ [listing.imovel?.bairro, listing.imovel?.cidade].filter(Boolean).join(', ') }}</span>
                            </p>
                            
                            <!-- Valores -->
                            <div class="mt-auto">
                                <div v-if="listing.imovel?.valor_venda" class="text-primary font-bold text-lg">
                                    {{ formatCurrency(listing.imovel.valor_venda) }}
                                </div>
                                <div v-else-if="listing.imovel?.valor_locacao" class="text-primary font-bold text-lg">
                                    {{ formatCurrency(listing.imovel.valor_locacao) }}<span class="text-sm font-normal">/mês</span>
                                </div>
                            </div>

                            <!-- Botão de ação -->
                            <Button 
                                class="mt-3 w-full" 
                                variant="outline"
                                @click="$inertia.visit(route('admin.corretor.listings.show', listing.id))"
                            >
                                Ver Detalhes
                            </Button>
                        </div>
                    </div>
                </SwiperSlide>

                <!-- Navegação personalizada -->
                <template #container-end>
                    <button class="custom-swiper-prev-listings nav-btn-listings" aria-label="Anterior">
                        <i class="fa-solid fa-angle-left"></i>
                    </button>
                    <button class="custom-swiper-next-listings nav-btn-listings" aria-label="Próximo">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                </template>
            </Swiper>
        </div>
    </div>
</template>

<style scoped>
.my-custom-swiper-listings {
    width: 100%;
    min-height: 450px;
    padding: 0 3rem !important;
    position: relative;
}

.swiper-slide {
    display: flex;
    justify-content: center;
    align-items: stretch;
    position: relative;
    height: auto;
}

/* Botões de navegação customizados */
.nav-btn-listings {
    position: absolute;
    top: 45%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid var(--border);
    border-radius: 50%;
    width: 3rem;
    height: 3rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    z-index: 10;
    font-size: 1.5rem;
    color: var(--primary);
}

.dark .nav-btn-listings {
    background: rgba(31, 41, 55, 0.9);
    border-color: var(--border);
}

.nav-btn-listings > i {
    font-size: 1.5rem;
}

.nav-btn-listings:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-50%) scale(1.05);
}

.custom-swiper-prev-listings {
    left: 0.5rem;
}

.custom-swiper-next-listings {
    right: 0.5rem;
}

@media (max-width: 768px) {
    .my-custom-swiper-listings {
        min-height: auto;
        padding: 0 1rem !important;
    }
    .nav-btn-listings {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1.2rem;
    }
    .nav-btn-listings > i {
        font-size: 1.2rem;
    }
    .custom-swiper-prev-listings {
        left: 0.25rem;
    }
    .custom-swiper-next-listings {
        right: 0.25rem;
    }
}
</style>
