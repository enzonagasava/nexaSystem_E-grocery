<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';


const props = defineProps<{
  produto: {
    id: number;
    title: string;
    image: string;
    tamanhos: { nome: string; preco: number }[];
  };
  selectedPortions: Record<number | string, string>;
}>();

const tamanhosArray = props.produto.tamanhos || [];

const emit = defineEmits(['selectPortion']);

const handleSelectPortion = (portionNome: string) => {
  emit('selectPortion', props.produto.id, portionNome);
};

</script>

<template>
  <Button
    v-for="(portion, index) in tamanhosArray"
    :key="index"
    @click="handleSelectPortion(portion.nome)"
    :class="{
      'border': true,
      'border-gray-400': props.selectedPortions[props.produto.id] !== portion.nome,
      'border-blue-500 bg-blue-100 text-blue-700': props.selectedPortions[props.produto.id] === portion.nome,
      'rounded-full': true,
      'px-3': true,
      'py-1': true,
      'text-xs': true,
      'cursor-pointer': true
    }"
  >
    {{ portion.nome }}
  </button>
</template>