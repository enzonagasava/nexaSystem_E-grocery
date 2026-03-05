<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ref, watch, computed } from 'vue'
import { vMaska } from 'maska/vue'

// register directive locally for this SFC
defineOptions({
  directives: {
    maska: vMaska,
  },
})
import { cn } from '@/lib/utils'
import { useVModel } from '@vueuse/core'
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue'

const props = defineProps<{
  id?: string
  name?: string
  label?: string
  tooltip?: string
  help?: string
  error?: string
  mask?: string | string[]
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
  (e: 'clear-error'): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const localError = ref<string | undefined>(props.error)

watch(() => props.error, (v: string | undefined) => {
  localError.value = v
})

watch(modelValue, () => {
  if (localError.value) {
    localError.value = undefined
    emits('clear-error')
  }
})

const autoMask = computed<any>(() => {
  if (props.mask) return props.mask
  const idOrName = (props.id || props.name || '').toString().toLowerCase()
  if (!idOrName) return undefined
  if (idOrName.includes('telefone') || idOrName.includes('phone')) {
    return '(##) #####-####'
  }
  if (idOrName.includes('cpf') ) {
    return '###.###.###-##'
  }
  if (idOrName.includes('cnpj')) {
    return '##.###.###/####-##'
  }
  if (idOrName.includes('rg')) {
    return '##.###.###-#'
  }
  return undefined
})
</script>

<template>
  <div>
    <LabelWithTooltip
      v-if="props.label && props.tooltip"
      :label="props.label"
      :tooltip="props.tooltip"
    />
    <label v-else-if="props.label" :for="props.id" class="block text-sm font-medium text-[var(--text-primary)] mb-1">{{ props.label }}</label>

    <input
      v-model="modelValue"
      :id="props.id"
      data-slot="input"
      v-bind="$attrs"
      v-maska="autoMask"
      :class="cn(
        'file:text-foreground placeholder:text-muted-foreground selection:bg-[var(--primary)] selection:text-[var(--primary-foreground)] border border-[var(--border)] flex h-9 w-full min-w-0 rounded-md bg-[var(--card)] px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-[var(--ring)] focus-visible:ring-[var(--ring)]/50 focus-visible:ring-[3px]',
        localError ? 'ring-[var(--destructive)]/30 border-[var(--destructive)]' : '',
        props.class,
      )"
      
    />

    <p v-if="props.help && !localError" class="text-sm text-[var(--text-secondary)] mt-1">{{ props.help }}</p>
    <p v-if="localError" class="text-sm text-red-600 dark:text-red-500 mt-1">{{ localError }}</p>
  </div>
</template>
