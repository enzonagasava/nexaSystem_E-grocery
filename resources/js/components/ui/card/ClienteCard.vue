<script setup lang="ts">
import type { Cliente } from '@/types/index'

defineProps<{
  cliente: Cliente
}>()
</script>

<template>
  <div
    class=" rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-all duration-200"
  >
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold text-gray-800">Cliente: <span class="text-gray-800 font-medium">{{ cliente.nome }}</span></h2>
      <span class="text-sm text-gray-500">#{{ cliente.id }}</span>
    </div>

    <!-- Informações principais -->
    <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
      <div>
        <label class="block text-gray-500 text-xs uppercase mb-0.5">Nome</label>
        <p class="text-gray-800 font-medium">{{ cliente.nome }}</p>
      </div>

      <div>
        <label class="block text-gray-500 text-xs uppercase mb-0.5">Número</label>
        <p class="text-gray-800 font-medium">{{ cliente.numero }}</p>
      </div>

      <div>
        <label class="block text-gray-500 text-xs uppercase mb-0.5">E-mail</label>
        <p class="text-gray-800 font-medium">{{ cliente.email }}</p>
      </div>
    </div>
  </div>
</template>
