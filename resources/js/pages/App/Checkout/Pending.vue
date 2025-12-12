<script setup lang="ts">
defineProps({
    pedidoId: String,
    status: String,
});

onMounted(() => {
    const timer = setInterval(async () => {
        const res = await fetch(`/api/pedido/status/${pedidoId}`);
        const pedido = await res.json();

        if (pedido.status === 'approved') {
            clearInterval(timer);
            window.location.href = '/checkout/sucesso';
        }

        if (pedido.status === 'rejected') {
            clearInterval(timer);
            window.location.href = '/checkout/falha';
        }
    }, 4000);
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-yellow-50">
        <div class="max-w-md rounded-xl bg-white p-8 text-center shadow-lg">
            <h1 class="mb-3 text-2xl font-bold text-yellow-600">Pagamento Pendente ⏳</h1>

            <p class="mb-4 text-gray-700">Estamos aguardando a confirmação do pagamento.</p>

            <div class="mb-4 rounded-md bg-gray-100 p-4 text-left">
                <p><strong>ID do Pagamento:</strong> {{ paymentId }}</p>
                <p><strong>Status:</strong> {{ status }}</p>
            </div>

            <a href="/" class="rounded-lg bg-yellow-600 px-6 py-3 text-white hover:bg-yellow-700"> Voltar ao início </a>
        </div>
    </div>
</template>
