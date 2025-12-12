<script setup lang="ts">
import ClienteCard from '@/components/ui/card/ClienteCard.vue';
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps({
    plataformas: Array,
    modelValue: Object,
    status: String,
    data: String,
    endereco: String,
    plataformaSelecionada: Number,
});

const emit = defineEmits(['update:modelValue', 'update:status', 'update:data', 'update:endereco', 'update:plataformaSelecionada']);

const search = ref('');
const clientes = ref<any[]>([]);

// --- usamos um proxy local vinculado ao modelValue ---
const clienteSelecionado = ref(props.modelValue ?? null);

watch(
    () => props.modelValue,
    (novo) => {
        clienteSelecionado.value = novo;
    },
);

// --- busca clientes ---
let controller: AbortController | null = null;

async function buscarClientes() {
    const termo = search.value.trim();

    if (!termo.length) {
        clientes.value = [];
        return;
    }

    if (controller) controller.abort();
    controller = new AbortController();

    try {
        const { data } = await axios.get(route('clientes.buscar'), {
            params: { search: termo },
            signal: controller.signal,
        });
        clientes.value = data;
    } catch (err: any) {
        if (err.name === 'CanceledError') return;
    }
}

// --- selecionar cliente ---
function selecionarCliente(c: any) {
    clienteSelecionado.value = c;
    emit('update:modelValue', c);
    search.value = c.nome;
    clientes.value = [];
}
</script>

<template>
    <div class="space-y-4">
        <div class="relative">
            <input
                v-model="search"
                type="search"
                placeholder="Buscar cliente..."
                class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                @input="buscarClientes"
            />
            <ul v-if="clientes.length" class="absolute z-10 mt-1 w-full rounded-lg border bg-white">
                <li v-for="c in clientes" :key="c.id" @click="selecionarCliente(c)" class="cursor-pointer px-3 py-2 hover:bg-gray-100">
                    {{ c.id }} — {{ c.nome }} — {{ c.email }}
                </li>
            </ul>
        </div>

        <div>
            <ClienteCard v-if="clienteSelecionado" :cliente="clienteSelecionado" />
            <div v-else class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-gray-500">
                Nenhum cliente selecionado ainda.
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4" v-if="clienteSelecionado">
            <div>
                <label class="mb-1 block text-sm font-medium">Endereço</label>
                <input
                    :value="props.endereco"
                    @input="emit('update:endereco', $event.target.value)"
                    type="text"
                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Plataforma</label>
                <select
                    :value="props.plataformaSelecionada"
                    @change="emit('update:plataformaSelecionada', $event.target.value)"
                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                >
                    <option value="">Selecione...</option>
                    <option v-for="p in props.plataformas" :key="p.id" :value="p.id">
                        {{ p.nome }}
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Data</label>
                <input
                    type="datetime-local"
                    :value="props.data"
                    @input="$emit('update:data', $event.target.value)"
                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Status</label>
                <select
                    :value="props.status"
                    @change="$emit('update:status', $event.target.value)"
                    class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                >
                    <option value="em-andamento">Em Andamento</option>
                    <option value="a-caminho">A Caminho</option>
                    <option value="finalizado">Finalizado</option>
                </select>
            </div>
        </div>
    </div>
</template>
