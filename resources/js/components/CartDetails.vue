<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

interface CartProps {
    cart: Record<string, any>;
}

const page = usePage<CartProps>();

// Converte o objeto do carrinho em um array para o `v-for`
const cartItems = ref(
    Object.entries(page.props.cart).map(([key, item]) => ({
        key,
        ...item,
    })),
);

const cepInput = ref('');

function removeItem(key: string) {
    axios
        .delete(route('carrinho.remover', { cartItemId: key }))
        .then((response) => {
            if (response.data.success) {
                // Atualiza o estado local cartItems
                cartItems.value = Object.entries(response.data.cart).map(([key, item]) => ({
                    key,
                    ...item,
                }));
            }
        })
        .catch((error) => {
            console.error('Erro ao remover item:', error);
        });
}

const calculateShipping = () => {
    if (cepInput.value.length === 8) {
        // Exemplo de validação simples para CEP
        alert(`Calculando frete para CEP: ${cepInput.value}`);
        // Lógica real de cálculo de frete (integração com API de frete)
    } else {
        alert('Por favor, insira um CEP válido com 8 dígitos.');
    }
};

// --- Computed Property para o Total ---
const cartTotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.preco * item.quantidade, 0);
});

function incrementQuantity(item: any) {
    if (item.quantidade < item.estoque) {
        item.quantidade = Math.floor(item.quantidade) + 1;
    } else {
        alert(`Quantidade máxima disponível em estoque: ${item.estoque}`);
    }
}

// Função para decrementar a quantidade (mínimo 1)
function decrementQuantity(item: any) {
    item.quantidade = Math.max(1, Math.floor(item.quantidade) - 1);
}
</script>

<template>
    <div class="flex min-h-screen justify-center px-4 py-12 text-black sm:px-6 lg:px-8">
        <div class="w-full max-w-4xl rounded-lg p-8 shadow-xl">
            <h1 class="mb-8 text-3xl font-bold text-black">Carrinho</h1>

            <div class="mb-4 hidden grid-cols-12 gap-4 border-b border-green-700 pb-2 font-semibold text-green-400 md:grid">
                <div class="col-span-6">Produto:</div>
                <div class="col-span-3 text-center">Quantidade:</div>
                <div class="col-span-2 text-right">Valor:</div>
                <div class="col-span-1"></div>
            </div>

            <div v-if="cartItems.length === 0" class="py-8 text-center text-black">Seu carrinho está vazio.</div>

            <div v-else class="space-y-6">
                <div
                    v-for="item in cartItems"
                    :key="item.key"
                    class="grid grid-cols-12 items-center gap-4 border-b border-gray-700 py-4 last:border-b-0"
                >
                    <div class="col-span-12 flex items-center md:col-span-6">
                        <img :src="item.thumbnail" :alt="item.name" class="mr-4 h-20 w-20 rounded-md border border-gray-600 object-cover" />
                        <div>
                            <p class="font-semibold text-black">{{ item.nome }}</p>
                            <p class="text-sm text-black">Porção: {{ item.porcao }}</p>
                        </div>
                    </div>

                    <div class="col-span-6 mt-2 flex items-center justify-center md:col-span-3 md:mt-0 md:justify-start">
                        <div class="flex items-center rounded-md border border-gray-600">
                            <button @click="decrementQuantity(item)" class="p-2 text-green-400 hover:text-green-500">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M7 10H13V11H7V10Z" /></svg>
                            </button>
                            <input
                                type="text"
                                v-model.number="item.quantidade"
                                class="w-10 border-x border-gray-600 bg-white text-center text-black focus:outline-none"
                                @change="item.quantidade = Math.max(1, Math.floor(item.quantidade))"
                            />
                            <button @click="incrementQuantity(item)" class="p-2 text-green-400 hover:text-green-500">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M13 9H11V7H10V9H8V10H10V12H11V10H13V9Z" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="col-span-6 mt-2 flex items-center justify-end pr-2 md:col-span-3 md:mt-0 md:pr-0">
                        <span class="mr-4 text-lg font-bold text-black">R$ {{ (item.preco * item.quantidade).toFixed(2).replace('.', ',') }}</span>
                        <button @click="removeItem(item.key)" class="text-red-500 transition-colors duration-200 hover:text-red-600">
                            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end border-t border-gray-700 pt-4">
                <span class="mr-4 text-xl font-bold text-black">Total:</span>
                <span class="text-3xl font-bold text-black">R$ {{ cartTotal.toFixed(2).replace('.', ',') }}</span>
            </div>

            <div class="mt-10">
                <h2 class="mb-4 text-xl font-bold text-black">Calcule o frete:</h2>
                <div class="flex flex-col items-center space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3">
                    <input
                        type="text"
                        v-model="cepInput"
                        placeholder="Digite o seu CEP"
                        class="w-full flex-grow rounded-md border border-gray-600 bg-white px-4 py-2 text-black placeholder-gray-400 focus:border-transparent focus:ring-2 focus:ring-green-500 focus:outline-none sm:w-auto"
                        maxlength="8"
                    />
                    <button
                        @click="calculateShipping"
                        class="w-full rounded-md bg-green-600 px-6 py-2 font-semibold text-black transition duration-300 hover:bg-green-700 sm:w-auto"
                    >
                        Calcular
                    </button>
                </div>
            </div>
            <div class="mt-8 flex h-64 items-center justify-center rounded-lg bg-white p-6 text-center text-black">[Localização ou Mapa aqui]</div>
            <Link
                :href="route('payment.index')"
                class="w-full rounded-md bg-green-600 px-6 py-2 font-semibold text-black transition duration-300 hover:bg-green-700 sm:w-auto"
            >
                Próximo
            </Link>
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos se necessário */
input[type='text']::-webkit-outer-spin-button,
input[type='text']::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type='text'] {
    -moz-appearance: textfield;
}
</style>
