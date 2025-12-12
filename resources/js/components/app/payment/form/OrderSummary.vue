<script setup lang="ts">
import { defineProps } from 'vue';

interface CartItem {
    key: string;
    nome: string;
    porcao?: string;
    quantidade: number;
    preco: number;
    thumbnail?: string;
}

defineProps<{
    cartItems: CartItem[];
    cartTotal: number;
}>();
</script>

<template>
    <section class="summary-section">
        <h2 class="font-bold">Resumo do Pedido</h2>
        <div v-for="item in cartItems" :key="item.key" class="flex items-center justify-between">
            <div class="flex">
                <img :src="item.thumbnail" :alt="item.nome" class="summary-item-thumbnail" />
                <div class="flex flex-col">
                    <span>{{ item.nome }}</span>
                    <span>Porção: {{ item.porcao }}</span>
                </div>
            </div>

            <span>Qtd: {{ item.quantidade }}</span>

            <span>R$ {{ Number(item.preco).toFixed(2).replace('.', ',') }}</span>
        </div>
        <div class="flex justify-between text-[18px] font-bold">
            <span>Total</span>
            <span>R$ {{ cartTotal.toFixed(2).replace('.', ',') }}</span>
        </div>
    </section>
</template>
<style scoped>
.summary-section {
    flex: 1;
}

.summary-section {
    background: #f9fafb;
    border-radius: 10px;
    padding: 20px 25px;
    box-shadow: inset 0 0 10px #e0e5e8;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

@media (max-width: 768px) {
    .summary-section {
        width: 100%;
    }
}

.summary-item-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    margin-right: 10px;
    border-radius: 4px;
}
</style>
