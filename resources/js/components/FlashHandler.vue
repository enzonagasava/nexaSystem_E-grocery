<script lang="ts" setup>
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'
import { useToastStore } from '@/stores/toast'

const page = usePage()
const toast = useToastStore()

// Observa mudanças nas flash messages do Inertia
watch(
  () => page.props.flash,
  (flash) => {
    if (!flash) return
    
    // Sucesso
    if (flash.success) {
      toast.show(flash.success, 'success')
      // Limpa para não mostrar novamente
      delete page.props.flash.success
    }
    
    // Erro
    if (flash.error) {
      toast.show(flash.error, 'error')
      delete page.props.flash.error
    }
    
    // Aviso
    if (flash.warning) {
      toast.show(flash.warning, 'warning')
      delete page.props.flash.warning
    }
    
    // Info
    if (flash.info) {
      toast.show(flash.info, 'info')
      delete page.props.flash.info
    }
  },
  { deep: true, immediate: true }
)
</script>

<template>
  <!-- Componente apenas lógico -->
</template>