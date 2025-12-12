<script setup lang="ts">
defineProps({
    pedido: {
        type: Object,
        required: true,
    },
});

const formatStatus = (status: string) => {
    if (!status) return '';
    return status.replace(/-/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<template>
    <div class="space-y-6 rounded-2xl bg-white p-6 shadow">
        <h1 class="text-2xl font-bold">Pedido #{{ pedido.cod_pedido }}</h1>

        <!-- Cliente -->
        <section>
            <h2 class="mb-2 text-lg font-semibold">Cliente</h2>
            <div class="space-y-1 text-gray-700">
                <p><strong>Nome:</strong> {{ pedido.cliente?.nome }}</p>
                <p><strong>Email:</strong> {{ pedido.cliente?.email }}</p>
            </div>
        </section>

        <!-- Produtos -->
        <section>
            <h2 class="mb-2 text-lg font-semibold">Produtos</h2>
            <table class="w-full rounded-lg border border-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Produto</th>
                        <th class="px-4 py-2 text-center">Quantidade</th>
                        <th class="px-4 py-2 text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in pedido.cod_pedidos" :key="item.id" class="border-t">
                        <td class="px-4 py-2">{{ item.produto?.nome || '—' }}</td>
                        <td class="px-4 py-2 text-center">{{ item.quantidade }}</td>
                        <td class="px-4 py-2 text-right">R$ {{ item.valor_pedido }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Resumo -->
        <section class="text-right">
            <p class="text-gray-700"><strong>Status:</strong> {{ formatStatus(pedido.status) }}</p>
            <p class="mt-2 text-xl font-semibold">Total: R$ {{ pedido.valor.toFixed(2) }}</p>
        </section>
    </div>
</template>
