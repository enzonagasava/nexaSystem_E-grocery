<script setup lang="ts">
import { computed, useAttrs, ref } from 'vue'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Primitive, type PrimitiveProps } from 'reka-ui'
import { type ButtonVariants, buttonVariants } from '.'
import { useLoadingStore } from '@/stores/loading'

interface Props extends PrimitiveProps {
  variant?: ButtonVariants['variant']
  size?: ButtonVariants['size']
  class?: HTMLAttributes['class']
  /* Props de loading unificadas (opcionais) */
  loading?: boolean
  loadingPosition?: 'start' | 'center' | 'end'
  useGlobalLoading?: boolean
  spinnerSize?: string
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  loading: false,
  loadingPosition: 'start',
  useGlobalLoading: false,
  spinnerSize: 'w-4 h-4',
})

const attrs = useAttrs()
const loadingStore = useLoadingStore()

// Determina se deve usar loading global ou local
const effectiveLoading = computed(() =>
  props.useGlobalLoading ? loadingStore.isVisible : !!props.loading,
)

const isDisabled = computed(() => effectiveLoading.value || !!(attrs as any).disabled)
const spinnerClasses = computed(() => `${props.spinnerSize ?? 'w-4 h-4'} animate-spin text-current`)
// ID único para gradiente do spinner
const gradientId = ref(`nexaGradient_${Math.random().toString(36).slice(2, 9)}`)
</script>

<template>
  <Primitive
    data-slot="button"
    :as="props.as"
    :as-child="props.asChild"
    v-bind="attrs"
    :disabled="isDisabled"
    :aria-busy="effectiveLoading"
    :class="cn(buttonVariants({ variant: props.variant, size: props.size }), props.class, 'relative')"
  >
    <!-- Loading no início -->
    <template v-if="props.loadingPosition === 'start'">
      <Transition name="nexa-fade">
        <span v-if="effectiveLoading" class="inline-flex items-center mr-2">
          <svg :class="[spinnerClasses, 'nexa-x-logo', 'nexa-x-spin']" width="24" height="24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
              <linearGradient :id="gradientId" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#F5F5F5" />
                <stop offset="100%" stop-color="#6366F1" />
              </linearGradient>
            </defs>
            <path class="nexa-x-stroke nexa-x-left" d="M20 20 L45 50 L20 80" stroke="#F5F5F5" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            <path class="nexa-x-stroke nexa-x-right" d="M80 20 L55 50 L80 80" :stroke="`url(#${gradientId})`" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
          </svg>
        </span>
      </Transition>
      <slot />
    </template>

    <!-- Loading no fim -->
    <template v-else-if="props.loadingPosition === 'end'">
      <slot />
      <Transition name="nexa-fade">
        <span v-if="effectiveLoading" class="inline-flex items-center ml-2">
          <svg :class="[spinnerClasses, 'nexa-x-logo', 'nexa-x-spin']" width="24" height="24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
              <linearGradient :id="gradientId" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#F5F5F5" />
                <stop offset="100%" stop-color="#6366F1" />
              </linearGradient>
            </defs>
            <path class="nexa-x-stroke nexa-x-left" d="M20 20 L45 50 L20 80" stroke="#F5F5F5" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            <path class="nexa-x-stroke nexa-x-right" d="M80 20 L55 50 L80 80" :stroke="`url(#${gradientId})`" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
          </svg>
        </span>
      </Transition>
    </template>

    <!-- Loading centralizado -->
    <template v-else>
      <span :aria-hidden="true" class="opacity-0 inline-block w-full">
        <slot />
      </span>

      <Transition name="nexa-fade">
        <span v-if="effectiveLoading" class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <svg :class="[props.spinnerSize ?? 'w-4 h-4', 'nexa-x-logo', 'nexa-x-spin']" width="36" height="36" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
              <linearGradient :id="gradientId" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#F5F5F5" />
                <stop offset="100%" stop-color="#6366F1" />
              </linearGradient>
            </defs>
            <path class="nexa-x-stroke nexa-x-left" d="M20 20 L45 50 L20 80" stroke="#F5F5F5" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            <path class="nexa-x-stroke nexa-x-right" d="M80 20 L55 50 L80 80" :stroke="`url(#${gradientId})`" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none" />
          </svg>
          <span class="sr-only">Carregando</span>
        </span>
      </Transition>

      <slot v-if="!effectiveLoading" />
    </template>
  </Primitive>
</template>

<style scoped>
/* Pequenos helpers usados pelo spinner */
.sr-only {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
}

/* Nexa X small helpers para o spinner */
.nexa-x-logo {
  transform-origin: center;
}
.nexa-x-spin {
  animation: nexa-spin 1.2s cubic-bezier(0.68, -0.15, 0.32, 1.15) infinite;
}
.nexa-x-stroke {
  stroke-dasharray: 120;
  stroke-dashoffset: 0;
}
</style>
