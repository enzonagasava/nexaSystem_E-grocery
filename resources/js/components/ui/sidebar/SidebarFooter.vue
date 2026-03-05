<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import ThemeToggle from '@/components/ui/theme/ThemeToggle.vue'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    data-slot="sidebar-footer"
    data-sidebar="footer"
    :class="cn('flex flex-col gap-2 p-2', props.class)"
  >
    <div class="pt-2">
      <ThemeToggle />
    </div>
  </div>
</template>
