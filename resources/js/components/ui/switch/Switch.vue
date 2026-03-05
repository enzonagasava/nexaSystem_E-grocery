<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue'

const model = defineModel<boolean>({ default: false })

const props = defineProps<{
  id?: string
  name?: string
  label?: string
  tooltip?: string
  disabled?: boolean
  loading?: boolean
  class?: HTMLAttributes['class']
}>()

function toggle() {
  if (props.disabled || props.loading) return
  model.value = !model.value
}
</script>

<template>
  <div>
    <LabelWithTooltip
      v-if="props.label && props.tooltip"
      :label="props.label"
      :tooltip="props.tooltip"
    />
    <label v-else-if="props.label" :for="props.id" class="block text-sm font-medium text-[var(--text-primary)] mb-1">{{ props.label }}</label>

    <button
      type="button"
      role="switch"
      :aria-checked="model"
      :disabled="props.disabled || props.loading"
      :class="cn(
        'relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        model ? 'bg-primary' : 'bg-muted',
        props.class
      )"
      @click.prevent="toggle"
    >
    <span
      aria-hidden="true"
      :class="cn(
        'pointer-events-none absolute top-0.5 left-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-white shadow transform transition-transform duration-200',
        model ? 'translate-x-5' : 'translate-x-0'
      )"
    >
      <span
        v-if="loading"
        class="h-3 w-3 animate-spin rounded-full border-2 border-gray-200 border-t-primary"
      />
    </span>
    </button>
  </div>
</template>
