<script setup lang="ts">
import { ref } from 'vue'
import { Eye, EyeOff } from 'lucide-vue-next'
import { Button } from '.'

const modelValue = defineModel<boolean>({ default: false })

const toggle = () => {
  modelValue.value = !modelValue.value
}
</script>

<template>
  <Button
    type="button"
    variant="ghost"
    size="icon"
    class="absolute inset-y-0 right-2 flex items-center justify-center text-gray-500 hover:text-gray-700"
    @click="toggle"
  >
    <Eye v-if="!modelValue" class="w-4 h-4" />
    <EyeOff v-else class="w-4 h-4" />
  </Button>
</template>
