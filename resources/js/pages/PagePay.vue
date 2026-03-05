<script setup lang="ts">
import PaymentDetails from '@/components/app/payment/PaymentDetails.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

interface CartProps {
    cart: Record<string, any>;
}

const page = usePage<CartProps>();
const cart = page.props.cart;
</script>

<template>
    <Head>
        <title>Família Mogi - Pagamento</title>
        <meta name="description" content="Produtos da Família Mogi" />
    </Head>
    <AppLayout>
        <PaymentDetails :cart="cart" />
    </AppLayout>
</template>
