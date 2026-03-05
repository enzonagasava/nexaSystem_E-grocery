<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { onClickOutside } from '@vueuse/core'
import Input from '@/components/ui/input/Input.vue'
import { cn } from '@/lib/utils'

/**
 * Remove acentos de uma string para comparação insensível a acentos
 */
function removeAccents(str: string): string {
  return str
    .normalize('NFD')
    .replace(/[\p{M}]/gu, '')
    .toLowerCase()
}

interface Props {
  id?: string
  label?: string
  modelValue?: string
  suggestions: string[]
  placeholder?: string
  help?: string
  error?: string
  isLoading?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  suggestions: () => [],
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'search', term: string): void
  (e: 'select', value: string): void
}>()

const isFocused = ref(false)
const autocompleteRef = ref<HTMLDivElement>()
const selectedIndex = ref(-1)

/**
 * Filtra sugestões baseado no valor atual do input com remoção de acentos
 */
const filteredSuggestions = computed(() => {
  if (!props.modelValue || props.modelValue.length < 2) {
    return props.suggestions
  }

  const normalized = removeAccents(props.modelValue)
  return props.suggestions.filter((suggestion: string) =>
    removeAccents(suggestion).includes(normalized)
  )
})

const showSuggestions = computed(() => {
  return isFocused.value && filteredSuggestions.value.length > 0
})

function handleInput(value: string | number) {
  const stringValue = String(value)
  emit('update:modelValue', stringValue)
  emit('search', stringValue)
  selectedIndex.value = -1
}

function handleFocus() {
  isFocused.value = true
  // Emite search ao focar para carregar sugestões
  emit('search', props.modelValue || '')
}

function selectSuggestion(suggestion: string) {
  emit('update:modelValue', suggestion)
  emit('select', suggestion)
  isFocused.value = false
  selectedIndex.value = -1
}

function handleKeydown(event: KeyboardEvent) {
  if (!showSuggestions.value) return

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      selectedIndex.value = Math.min(
        selectedIndex.value + 1,
        filteredSuggestions.value.length - 1
      )
      break
    case 'ArrowUp':
      event.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, -1)
      break
    case 'Enter':
      event.preventDefault()
      if (selectedIndex.value >= 0) {
        selectSuggestion(filteredSuggestions.value[selectedIndex.value])
      }
      break
    case 'Escape':
      isFocused.value = false
      selectedIndex.value = -1
      break
  }
}

onClickOutside(autocompleteRef, () => {
  isFocused.value = false
  selectedIndex.value = -1
})
</script>

<template>
  <div ref="autocompleteRef" class="relative">
    <Input
      :id="id"
      :label="label"
      :model-value="modelValue"
      :placeholder="placeholder"
      :help="help"
      :error="error"
      :class="class"
      @update:modelValue="handleInput"
      @focus="handleFocus"
      @keydown="handleKeydown"
      autocomplete="off"
    />

    <!-- Dropdown de sugestões -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showSuggestions"
        class="absolute z-50 w-full mt-1 bg-[var(--card)] border border-[var(--border)] rounded-md shadow-lg max-h-60 overflow-auto"
      >
        <div
          v-for="(suggestion, index) in filteredSuggestions"
          :key="suggestion"
          :class="cn(
            'px-3 py-2 cursor-pointer transition-colors text-sm',
            index === selectedIndex
              ? 'bg-[var(--accent)] text-[var(--accent-foreground)]'
              : 'hover:bg-[var(--accent)] hover:text-[var(--accent-foreground)]'
          )"
          @click="selectSuggestion(suggestion)"
        >
          {{ suggestion }}
        </div>

        <div v-if="isLoading" class="px-3 py-2 text-sm text-muted-foreground text-center">
          Carregando...
        </div>
      </div>
    </Transition>
  </div>
</template>
