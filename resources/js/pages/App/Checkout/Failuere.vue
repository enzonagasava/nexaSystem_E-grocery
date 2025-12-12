<script setup lang="ts">
defineProps({
    status: String,
    message: String,
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-red-50">
        <div class="max-w-md rounded-xl bg-white p-8 text-center shadow-lg">
            <h1 class="mb-3 text-2xl font-bold text-red-600">Pagamento Falhou ❌</h1>

            <p class="mb-4 text-gray-700">Parece que houve um problema ao processar seu pagamento.</p>

            <div class="mb-4 rounded-md bg-gray-100 p-4 text-left">
                <p><strong>Status:</strong> {{ status || 'erro' }}</p>
                <p><strong>Mensagem:</strong> {{ message || 'O pagamento não foi concluído.' }}</p>
            </div>

            <a href="/carrinho" class="rounded-lg bg-red-600 px-6 py-3 text-white hover:bg-red-700"> Tentar novamente </a>
        </div>
    </div>
</template>
