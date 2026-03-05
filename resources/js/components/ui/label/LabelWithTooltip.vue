<script setup lang="ts">
import { Label } from '@/components/ui/label'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { CircleHelp } from 'lucide-vue-next'

defineProps<{
  /** Texto da label */
  label: string
  /** Texto da tooltip explicativa */
  tooltip: string
  /** Classes CSS adicionais para a label */
  class?: string
}>()
</script>

<template>
  <div class="flex items-center gap-2 mb-1">
    <Label :class="class">{{ label }}</Label>
    <TooltipProvider :delay-duration="200">
      <Tooltip>
        <TooltipTrigger as-child>
          <CircleHelp class="h-4 w-4 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 cursor-help transition-colors" />
        </TooltipTrigger>
        <TooltipContent class="max-w-xs">
          <p class="text-sm">{{ tooltip }}</p>
        </TooltipContent>
      </Tooltip>
    </TooltipProvider>
  </div>
</template>
