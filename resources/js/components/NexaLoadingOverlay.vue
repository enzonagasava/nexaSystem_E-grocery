<script setup lang="ts">
import { useLoadingStore } from '@/stores/loading';
import { computed, ref, watch } from 'vue';

const loadingStore = useLoadingStore();

// Controla a fase da animação
const animationPhase = ref<'opening' | 'spinning'>('opening');

// Reinicia a animação quando loading aparecer
watch(
    () => loadingStore.isVisible,
    (visible) => {
        if (visible) {
            animationPhase.value = 'opening';
            // Após a animação de abrir/fechar, começa a girar
            setTimeout(() => {
                animationPhase.value = 'spinning';
            }, 1200); // Duração da animação open-close
        }
    },
);

const animationClass = computed(() => {
    return animationPhase.value === 'opening' ? 'nexa-x-open-close' : 'nexa-x-spin';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="nexa-fade">
            <div v-if="loadingStore.isVisible" class="nexa-loading-overlay">
                <div class="nexa-loading-container">
                    <!-- SVG do X da Nexa -->
                    <svg
                        :class="['nexa-x-logo', animationClass]"
                        width="80"
                        height="80"
                        viewBox="0 0 100 100"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <!-- Traço esquerdo do X (branco) -->
                        <path
                            class="nexa-x-stroke nexa-x-left"
                            d="M20 20 L45 50 L20 80"
                            stroke="#F5F5F5"
                            stroke-width="12"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            fill="none"
                        />
                        <!-- Traço direito do X (gradiente branco para roxo) -->
                        <defs>
                            <linearGradient id="nexaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#F5F5F5" />
                                <stop offset="100%" stop-color="#6366F1" />
                            </linearGradient>
                        </defs>
                        <path
                            class="nexa-x-stroke nexa-x-right"
                            d="M80 20 L55 50 L80 80"
                            stroke="url(#nexaGradient)"
                            stroke-width="12"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            fill="none"
                        />
                    </svg>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.nexa-loading-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
    background: rgba(99, 102, 241, 0.2);
}

.nexa-loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.nexa-x-logo {
    transform-origin: center;
}

/* Animação de abrir e fechar o X */
.nexa-x-open-close .nexa-x-stroke {
    stroke-dasharray: 120;
    stroke-dashoffset: 120;
    animation: nexa-draw-stroke 0.6s ease-out forwards, nexa-undraw-stroke 0.4s ease-in 0.7s forwards;
}

.nexa-x-open-close .nexa-x-right {
    animation-delay: 0.15s, 0.85s;
}

/* Animação de girar contínuo */
.nexa-x-spin {
    animation: nexa-spin 1.2s cubic-bezier(0.68, -0.15, 0.32, 1.15) infinite;
}

.nexa-x-spin .nexa-x-stroke {
    stroke-dasharray: 120;
    stroke-dashoffset: 0;
}

/* Texto com efeito de pulse */
.nexa-loading-text {
    display: flex;
    gap: 0.15rem;
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: 0.1em;
}

.nexa-loading-text span {
    color: #f5f5f5;
    animation: nexa-text-pulse 1.5s ease-in-out infinite;
}

.nexa-text-n {
    animation-delay: 0s;
}
.nexa-text-e {
    animation-delay: 0.1s;
}
.nexa-text-x {
    color: #6366f1 !important;
    animation-delay: 0.2s;
}
.nexa-text-a {
    animation-delay: 0.3s;
}

/* Transição fade do overlay */
.nexa-fade-enter-active {
    transition: opacity 0.2s ease-out;
}

.nexa-fade-leave-active {
    transition: opacity 0.3s ease-in;
}

.nexa-fade-enter-from,
.nexa-fade-leave-to {
    opacity: 0;
}
</style>
