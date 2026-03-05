<script setup lang="ts">
import { Primitive, type PrimitiveProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import { type ButtonVariants, buttonVariants } from '.'

interface Props extends PrimitiveProps {
  variant?: ButtonVariants['variant']
  size?: ButtonVariants['size']
  icon?: any 
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  variant: 'ghost',
  size: 'sm',
})
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :class="cn(
      buttonVariants({ variant, size }),
      'flex items-center gap-1 text-sm transition duration-150 ease-in-out cursor-pointer',
      props.class
    )"
  >
    <!-- Ícone -->
    <component
      v-if="icon"
      :is="icon"
      class="w-4 h-4"
    />

    <slot />
  </Primitive>
</template>
