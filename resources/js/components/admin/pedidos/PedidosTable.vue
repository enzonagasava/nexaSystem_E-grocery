<script setup lang="ts">
import { Button, ButtonTable } from '@/components/ui/button';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import type { Pedido } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Check, Eye, Pencil, ShoppingCart } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    pedidos: Pedido[];
    statusFiltro: string;
}>();

const pedidosFiltrados = computed(() => {
    if (props.statusFiltro === 'todos') {
        return props.pedidos;
    }
    return props.pedidos.filter((p) => p.status.toLowerCase() === props.statusFiltro);
});

const formatStatus = (status: string) => {
    if (!status) return '';
    return status.replace(/-/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

async function avancarStatus(pedidoId: number) {
    try {
        await axios.put(route('admin.pedidos.avancar.status', pedidoId));
        alert('✅ Pedido atualizado com sucesso!');
        router.reload();
    } catch (e) {
        console.error(e);
        alert('Erro ao avançar o status.');
    }
}
</script>

<template>
    <div class="mb-6 flex items-center justify-between">
        <HeadingSmall title="Gerenciar Pedidos" />

        <Link :href="route('admin.pedidos.create')">
            <Button> + Adicionar Novo Pedido </Button>
        </Link>
    </div>

    <div class="mb-6 flex gap-3">
        <Link :href="route('admin.pedidos.index')" :data="{ status: 'todos' }" preserve-state>
            <Button :variant="props.statusFiltro === 'todos' ? 'default' : 'outline'"> Todos </Button>
        </Link>
        <Link :href="route('admin.pedidos.index')" :data="{ status: 'em-andamento' }" preserve-state>
            <Button :variant="props.statusFiltro === 'em-andamento' ? 'default' : 'outline'"> Em Andamento </Button>
        </Link>
        <Link :href="route('admin.pedidos.index')" :data="{ status: 'a-caminho' }" preserve-state>
            <Button :variant="props.statusFiltro === 'a-caminho' ? 'default' : 'outline'"> A Caminho </Button>
        </Link>
        <Link :href="route('admin.pedidos.index')" :data="{ status: 'finalizado' }" preserve-state>
            <Button :variant="props.statusFiltro === 'finalizado' ? 'default' : 'outline'"> Finalizados </Button>
        </Link>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full rounded-xl bg-white shadow">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Código Pedido</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Endereço</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Plataforma</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                <tr v-for="pedido in pedidosFiltrados" :key="pedido.id" class="transition-colors hover:bg-gray-50">
                    <td class="text-center">{{ pedido.cliente }}</td>
                    <td class="text-center">{{ pedido.cod_pedido }}</td>
                    <td class="text-center">{{ pedido.valor }}</td>
                    <td class="text-center">{{ pedido.endereco }}</td>
                    <td class="text-center">{{ pedido.plataforma }}</td>
                    <td class="text-center capitalize">
                        <span
                            :class="[
                                'rounded-full px-3 py-1 text-xs font-semibold',
                                pedido.status.toLowerCase() === 'em-andamento'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : pedido.status.toLowerCase() === 'a-caminho'
                                      ? 'bg-blue-100 text-blue-800'
                                      : 'bg-green-100 text-green-800',
                            ]"
                        >
                            {{ formatStatus(pedido.status) }}
                        </span>
                    </td>
                    <td class="text-center">{{ pedido.created_at_formatted }}</td>
                    <td class="flex justify-end gap-3 px-6 py-4 text-right text-sm font-medium">
                        <Link :href="route('admin.pedidos.edit', pedido.id)">
                            <ButtonTable :icon="Pencil" label="Editar" variant="ghost" class="text-indigo-600 hover:text-indigo-900" />
                        </Link>
                        <Link :href="route('admin.pedidos.view', pedido.id)">
                            <ButtonTable :icon="Eye" label="Ver" variant="ghost" class="text-gray-700 hover:text-gray-900" />
                        </Link>
                        <ButtonTable
                            v-if="pedido.status !== 'finalizado'"
                            :icon="pedido.status.toLowerCase() === 'em-andamento' ? ShoppingCart : Check"
                            :label="pedido.status === 'em-andamento' ? 'Enviar' : 'Finalizar'"
                            variant="ghost"
                            class="text-green-600 hover:text-green-900"
                            @click="avancarStatus(pedido.id)"
                        />
                    </td>
                </tr>

                <tr v-if="pedidosFiltrados.length === 0">
                    <td colspan="8" class="py-6 text-center text-gray-500">Nenhum pedido encontrado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
