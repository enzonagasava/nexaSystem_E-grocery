<script setup lang="ts">
import ClienteModal from '@/components/admin/clientes/ClientesModal.vue';
import { Button, ButtonTable } from '@/components/ui/button';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Eye, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const page = usePage();
const clientes = ref(Array.isArray(page.props.clientes) ? page.props.clientes : []);

const showModal = ref(false);
const selectedCliente = ref(null);

function verCliente(cliente: any) {
    selectedCliente.value = cliente;
    showModal.value = true;
}
</script>

<template>
    <div class="mb-6 flex items-center justify-between">
        <HeadingSmall title="Gerenciar Clientes" />
        <Link :href="route('adicionar.clientes')">
            <Button> + Adicionar Novo Cliente </Button>
        </Link>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Id</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Nome</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Número</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">E-mail</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Endereço</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Data de criação</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr v-for="cliente in clientes" :key="cliente.id" class="hover:bg-gray-50">
                    <td class="text-center">
                        {{ cliente.id }}
                    </td>
                    <td class="text-center">
                        {{ cliente.nome }}
                    </td>
                    <td class="text-center">
                        {{ cliente.numero }}
                    </td>
                    <td class="text-center">
                        {{ cliente.email }}
                    </td>
                    <td class="text-center">
                        {{ cliente.endereco_completo }}
                    </td>
                    <td class="text-center">
                        {{ cliente.created_at_formatted }}
                    </td>
                    <td class="flex justify-end gap-3 px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                        <Link :href="route('editar.clientes', cliente.id)">
                            <ButtonTable :icon="Pencil" label="Editar" variant="ghost" class="text-indigo-600 hover:text-indigo-900" />
                        </Link>
                        <ButtonTable :icon="Eye" label="Ver" variant="ghost" class="text-red-600 hover:text-red-900" @click="verCliente(cliente)" />
                    </td>
                </tr>
                <tr v-if="clientes.length === 0">
                    <td colspan="3" class="py-6 text-center text-gray-500">Nenhum cliente encontrado.</td>
                </tr>
            </tbody>
        </table>
        <ClienteModal :show="showModal" :cliente="selectedCliente" @close="showModal = false" />
    </div>
</template>
