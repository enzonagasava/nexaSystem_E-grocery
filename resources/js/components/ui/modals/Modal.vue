<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, useAttrs } from 'vue'
import { cn } from '@/lib/utils'
import {
  Dialog,
  DialogScrollContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'

interface Props {
  modelValue?: boolean
  size?: 'sm' | 'md' | 'lg' | 'full'
  title?: string
  showClose?: boolean
  closeOnEsc?: boolean
  closeOnBackdrop?: boolean
  centered?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  size: 'md',
  showClose: true,
  closeOnEsc: true,
  closeOnBackdrop: true,
  centered: true,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'close'): void
}>()

const attrs = useAttrs()
const isOpen = ref(!!props.modelValue)

watch(
  () => props.modelValue,
  (v: boolean | undefined) => {
    isOpen.value = !!v
  },
)

watch(isOpen, (v: boolean) => {
  emit('update:modelValue', !!v)
  if (!v) emit('close')
})

function close() {
  if (!isOpen.value) return
  isOpen.value = false
}

function onKeydown(e: KeyboardEvent) {
  if (props.closeOnEsc && e.key === 'Escape') close()
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))

const panelClasses = computed(() => {
  const base = 'bg-background  rounded-lg shadow-lg overflow-hidden'
  const sizes: Record<string, string> = {
    sm: 'max-w-md w-full',
    md: 'max-w-xl w-full',
    lg: 'max-w-3xl w-full',
    full: 'w-full h-full rounded-none',
  }
  return cn(base, sizes[props.size || 'md'], props.class, (attrs && attrs.class) || '')
})
</script>

<template>
  <Dialog :open="isOpen" @update:open="(v: boolean) => isOpen = v">
    <DialogScrollContent :class="panelClasses" :showClose="false">
      <DialogHeader class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
          <DialogTitle v-if="props.title" class="text-lg font-semibold truncate">{{ props.title }}</DialogTitle>
          <DialogDescription>
            <slot name="subtitle" />
          </DialogDescription>
        </div>
      </DialogHeader>

      <div class="py-2">
        <slot />
      </div>

      <DialogFooter>
        <slot name="footer" />
      </DialogFooter>
    </DialogScrollContent>
  </Dialog>
</template>

<style scoped>
.nexa-modal-scale-enter-from {
  transform: translateY(8px) scale(0.98);
  opacity: 0;
}
.nexa-modal-scale-enter-to {
  transform: translateY(0) scale(1);
  opacity: 1;
}
.nexa-modal-scale-leave-to {
  transform: translateY(6px) scale(0.98);
  opacity: 0;
}

[role="dialog"]:focus {
  outline: none;
}
</style>
