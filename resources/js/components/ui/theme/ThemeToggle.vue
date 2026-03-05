<template>
  <Button @click="toggle" :aria-pressed="isDark" title="Alternar tema">
    <Moon v-if="isDark" class="w-5 h-5" />
    <Sun v-else class="w-5 h-5" />
  </button>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Sun, Moon } from 'lucide-vue-next'
const key = 'appearance'
const isDark = ref(false)

function apply(dark: boolean) {
  if (dark) document.documentElement.classList.add('dark')
  else document.documentElement.classList.remove('dark')
  try { localStorage.setItem(key, dark ? 'dark' : 'light') } catch (e) {}
  isDark.value = dark
}

function toggle() {
  apply(!isDark.value)
}

onMounted(() => {
  try {
    const stored = localStorage.getItem(key)
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
    const initial = stored === 'dark' || (stored === null && prefersDark)
    apply(initial)
  } catch (e) {}
})
</script>