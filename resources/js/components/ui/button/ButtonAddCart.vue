<script setup lang="ts">
import { ref, nextTick } from 'vue';
import axios from 'axios';
import { useCartStore } from '@/stores/cart';
import { Inertia } from '@inertiajs/inertia';
import { useToastStore } from '@/stores/toast';

interface Produto {
  id: number;
  name: string;
}

const props = withDefaults(
  defineProps<{
    produto: Produto;
    portion: string;
    price?: number;
    quantidade: number;
    title: string;
    redirectToCart?: boolean; 
  }>(),
  { price: 0 }
);

const emit = defineEmits<{
  (e: 'add-to-cart', produto: Produto, portion: string, quantidade: number, title: string): void;
}>();

const toast = useToastStore();

const cartStore = useCartStore();
const isLoading = ref(false);

const addToCart = async (e: SubmitEvent) => {
  e.preventDefault();
  if (isLoading.value) return;

  if (!props.portion || props.portion.trim() === '') {
    toast.show('Por favor, selecione uma porção antes de adicionar ao carrinho.', 'error');
    return;
  }

  isLoading.value = true;

  try {
    const { data } = await axios.post('/cart/add', {
      id: props.produto.id,
      porcao: props.portion,
      preco: props.price,
      quantidade: props.quantidade,
    });

    toast.show('Produto adicionado com sucesso!', 'success')
    cartStore.setCart(Object.values(data.cart));
    emit('add-to-cart', props.produto, props.portion);

    if (props.redirectToCart) {
      Inertia.visit(route('carrinho.index'));
    }
  } catch (error) {
      if (error.response && error.response.status === 400) {
        const mensagemErro = error.response.data.message;
        toast.show('Limite atingido: não há mais unidades disponíveis em estoque.', 'error')
      } else {
        // Outros erros
        alert('Erro inesperado ao adicionar produto.');
        console.error(error);
      }  
  } finally {
    isLoading.value = false;
  }
};

</script>

<template>
  <form @submit="addToCart">
    <Button
      type="submit"
      :disabled="isLoading"
      class="bg-[#6aab9c] text-white rounded-md px-4 py-3 text-white font-semibold mb-2 hover:bg-[#77bdad] transition cursor-pointer disabled:opacity-50 w-full"
    >
      {{ isLoading ? 'Adicionando…' : props.title }}
    </button>
  </form>
</template>