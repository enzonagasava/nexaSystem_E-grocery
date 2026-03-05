<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { ref, watch } from 'vue'
import { useVModel } from '@vueuse/core'
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue'


const props = defineProps<{
  id?: string
  label?:string
  tooltip?: string
  help?: string
  error?: string
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
  options?: Array<{ value: string | number; label: string }>
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
</script>

<template>
  <LabelWithTooltip
    v-if="props.label && props.tooltip"
    :label="props.label"
    :tooltip="props.tooltip"
  />
  <label v-else-if="props.label" :for="props.id" class="block text-sm font-medium text-[var(--text-primary)] mb-1">{{ props.label }}</label>

  <select
      v-model="modelValue"
      v-bind="$attrs"
      :class="cn(
        'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 block w-full rounded-md text-base shadow-xs transition-colors outline-none',
        'bg-[var(--card)] text-[var(--text-primary)]',
        'focus-visible:border-[var(--ring)] focus-visible:ring-[var(--ring)]/50 focus-visible:ring-[3px]',
        localError ? 'ring-[var(--destructive)]/30 border-[var(--destructive)]' : '',
        props.class,
      )"
    >
    <slot>
      <option v-for="opt in props.options || []" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </slot>
  </select>
  <p v-if="props.help && !localError" class="text-sm text-[var(--text-secondary)] mt-1">{{ props.help }}</p>
  <p v-if="localError" class="text-sm text-red-600 dark:text-red-500 mt-1">{{ localError }}</p>
</template>
