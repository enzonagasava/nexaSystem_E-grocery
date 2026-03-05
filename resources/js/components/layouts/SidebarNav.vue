<script setup lang="ts">
import ClinicaNavigation from '@/components/layouts/navigation/ClinicaNavigation.vue'
import CorretorNavigation from '@/components/layouts/navigation/CorretorNavigation.vue'
import EcommerceNavigation from '@/components/layouts/navigation/EcommerceNavigation.vue'
import { usePage } from '@inertiajs/vue3'

const emit = defineEmits(['close'])
const page = usePage()
</script>

<template>
  <div role="navigation" aria-label="Sidebar navigation">
    <ClinicaNavigation v-if="page.props.modulo === 'clinica'" @close="$emit('close')" />
    <CorretorNavigation v-else-if="page.props.modulo === 'corretor'" @close="$emit('close')" />
    <EcommerceNavigation v-else @close="$emit('close')" />
  </div>
</template>
