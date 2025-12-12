<script setup lang="ts">
import { computed, ref } from 'vue';
import BuyerForm from './form/BuyerForm.vue';
import OrderSummary from './form/OrderSummary.vue';

interface Props {
    cart: Record<string, any>;
}
const props = defineProps<Props>();

const cartItems = ref(
    Object.entries(props.cart).map(([key, item]) => ({
        key,
        ...item,
    })),
);

const cartTotal = computed(() => cartItems.value.reduce((sum, item) => sum + item.preco * item.quantidade, 0));

const message = computed(() => {
    if (cartItems.value.length === 0) {
        return `Olá, gostaria de fazer um pedido, mas meu carrinho está vazio.`;
    }

    let texto = `Olá, gostaria de fazer um pedido com os seguintes itens:\n\n`;

    cartItems.value.forEach((item) => {
        const nome = item.nome || item.key || 'Item';
        const porcao = item.porcao ? `Porção: ${item.porcao}` : '';
        const quantidade = item.quantidade ?? item.qtd ?? 1;
        const preco = item.preco ?? 0;
        const totalItem = preco * quantidade;

        texto += `- ${nome} ${porcao}\n`;
        texto += `  Quantidade: ${quantidade}\n`;
        texto += `  Preço unitário: R$ ${preco}\n`;
        texto += `  Total: R$ ${totalItem.toFixed(2)}\n\n`;
    });

    texto += `Valor total do pedido: R$ ${cartTotal.value.toFixed(2)}\n\n`;

    if (form.name.trim()) {
        texto += `Cliente: ${form.name}\n`;
    }

    texto += `Por favor, entre em contato para confirmar o pedido. Obrigado!`;

    return texto;
});

function fazerPedidoWhatsapp() {
    const telefone = '5511941560613';
    const url = `https://api.whatsapp.com/send?phone=${telefone}&text=${encodeURIComponent(message.value)}`;
    window.open(url, '_blank');
}
</script>

<template>
    <div class="container">
        <BuyerForm :form="form" @whatsapp="fazerPedidoWhatsapp" :cartItems="cartItems" :cartTotal="cartTotal" />
        <OrderSummary :cartItems="cartItems" :cartTotal="cartTotal" />
    </div>
</template>

<style scoped>
.container {
    background: white;
    max-width: 900px;
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 40px;
    padding: 30px;
    margin: 40px auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

@media (max-width: 768px) {
    .container {
        flex-direction: column;
        padding: 20px;
    }
}
</style>
