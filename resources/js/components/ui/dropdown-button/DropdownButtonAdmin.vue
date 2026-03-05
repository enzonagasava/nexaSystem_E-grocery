<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  label: string
  openByDefault?: boolean
  icon?: any
}

const props = defineProps<Props>()
const isOpen = ref(!!props.openByDefault)
</script>

<template>
    <div class="w-full">
    <Button
      @click="isOpen = !isOpen"
      class="flex w-full items-center justify-between rounded px-2 py-2 hover:bg-purple-900 transition"
    >
      <span class="flex items-center gap-2">
        <component :is="icon" class="w-4 h-4" />
        {{ label }}
      </span>

      <svg
        class="h-4 w-4 fill-current transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
      >
        <path
          d="M5.516 7.548a.75.75 0 011.06 0L10 10.97l3.424-3.423a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 010-1.06z"
        />
      </svg>
    </button>

    <div
      class="overflow-hidden transition-all duration-300 ease-in-out"
      :class="isOpen ? 'max-h-96 opacity-100 mt-1' : 'max-h-0 opacity-0'"
    >
      <slot />
    </div>
  </div>
</template>
