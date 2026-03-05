<script setup lang="ts">
import { cn } from '@/lib/utils'
import { X } from 'lucide-vue-next'
import {
  DialogClose,
  DialogContent,
  type DialogContentEmits,
  type DialogContentProps,
  DialogOverlay,
  DialogPortal,
  useForwardPropsEmits,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = withDefaults(defineProps<DialogContentProps & { class?: HTMLAttributes['class'], showClose?: boolean }>(), {
  showClose: true,
})
const emits = defineEmits<DialogContentEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)

function handlePointerDownOutside(event: any) {
  const originalEvent = event.detail?.originalEvent;
  if (!originalEvent) return;
  const target = originalEvent.target as HTMLElement;
  
  // Don't close modal if click is on a datepicker popup or other teleported content
  const datepickerPopups = document.querySelectorAll('[data-datepicker-popup]')
  for (const popup of datepickerPopups) {
    if (popup.contains(target)) {
      event.preventDefault()
      return
    }
  }
  
  // Only close if click is truly outside (offset beyond element bounds)
  if (originalEvent.offsetX > target.clientWidth || originalEvent.offsetY > target.clientHeight) {
    event.preventDefault();
  }
}
</script>

<template>
  <DialogPortal>
    <DialogOverlay
      class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/80  data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
    >
        <DialogContent
        :class="
          cn(
            'relative z-50 grid w-full my-8 gap-4 border border-border bg-background p-6 shadow-lg duration-200 sm:rounded-lg md:w-full',
            props.class,
          )
        "
        v-bind="forwarded"
        @pointer-down-outside="handlePointerDownOutside"
      >
        <slot />
        <DialogClose
          v-if="props.showClose"
          class="absolute -top-2 -right-2 w-8 h-8 p-1 flex items-center justify-center transition-all duration-200 rounded-lg bg-muted hover:bg-primary/80 hover:scale-90 cursor-pointer" 
        >
          <X class="w-4 h-4" />
          <span class="sr-only">Close</span>
        </DialogClose>
      </DialogContent>
    </DialogOverlay>
  </DialogPortal>
</template>