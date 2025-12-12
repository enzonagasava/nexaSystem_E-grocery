<script setup lang="ts">
defineProps({
    paymentId: String,
    status: String,
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-green-50">
        <div class="max-w-md rounded-xl bg-white p-8 text-center shadow-lg">
            <h1 class="mb-3 text-2xl font-bold text-green-600">Pagamento Aprovado! 🎉</h1>

            <p class="mb-4 text-gray-700">Obrigado por sua compra! Seu pagamento foi processado com sucesso.</p>

            <div class="mb-4 rounded-md bg-gray-100 p-4 text-left">
                <p><strong>ID do Pagamento:</strong> {{ paymentId }}</p>
                <p><strong>Status:</strong> {{ status }}</p>
            </div>

            <a href="/" class="rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700"> Voltar ao início </a>
        </div>
    </div>
</template>
