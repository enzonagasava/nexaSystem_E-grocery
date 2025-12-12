<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    pedido: {
        type: Object,
        required: true,
    },
});

const carregando = ref(false);
const erro = ref<string | null>(null);

async function salvarPedido() {
    try {
        carregando.value = true;
        erro.value = null;

        // se tiver um id, é edição (update), senão é criação (store)
        if (props.pedido.id) {
            await axios.put(route('admin.pedidos.update', props.pedido.id), props.pedido);
            alert('✅ Pedido atualizado com sucesso!');
        } else {
            await axios.post(route('admin.pedidos.store'), props.pedido);
            alert('✅ Pedido criado com sucesso!');
        }

        router.visit(route('admin.pedidos.index', props.pedido.status));
    } catch (e: any) {
        console.error(e);
        erro.value = e.response?.data?.message || 'Erro ao salvar o pedido.';
    } finally {
        carregando.value = false;
    }
}
</script>

<template>
    <div class="space-y-3 border-t pt-4">
        <p class="text-right font-medium">
            Total:
            <strong>R$ {{ props.pedido.valorTotal?.toFixed(2) || '0,00' }}</strong>
        </p>

        <div v-if="erro" class="text-right text-red-500">{{ erro }}</div>

        <div class="text-right">
            <Button @click="salvarPedido" :disabled="carregando" class="w-40 justify-center">
                <span v-if="carregando">
                    {{ props.pedido.id ? 'Atualizando...' : 'Salvando...' }}
                </span>
                <span v-else>
                    {{ props.pedido.id ? 'Atualizar Pedido' : 'Salvar Pedido' }}
                </span>
            </Button>
        </div>
    </div>
</template>
