<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const products = ref(Array.isArray(page.props.products) ? page.props.products : []);

const deleteProduct = (productId: number) => {
    if (confirm(`Tem certeza de que deseja excluir o produto ID: ${productId}?`)) {
        alert(`Excluindo o produto ID: ${productId}`);
        Inertia.delete(`/produtos/delete-produto/${productId}`, {});
        products.value = products.value.filter((p) => p.id !== productId);
    }
};
</script>

<template>
    <Head>
        <title>Gerenciar Produtos - Família Mogi</title>
        <meta name="description" content="Gerenciamento de produtos da Família Mogi" />
    </Head>
    <AuthLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-bold text-gray-800">Gerenciar Produtos</h1>
                            <Link
                                :href="route('produtos.create')"
                                class="focus:ring-opacity-50 rounded-md bg-green-600 px-4 py-2 text-white transition duration-150 ease-in-out hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none"
                            >
                                + Adicionar Novo Produto
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Produto
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Descrição do Produto
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img :src="product.imageUrl" :alt="product.name" class="h-16 w-16 rounded-md object-cover" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <span class="font-extrabold">Titulo: </span>{{ product.nome }}
                                            </div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <span class="font-extrabold">Descrição: </span>{{ product.descricao }}
                                            </div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <span class="font-extrabold">Estoque: </span>{{ product.estoque }}
                                            </div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <span class="font-extrabold">Data de criação: </span>{{ product.created_at }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                            <Link
                                                :href="route('produtos.edit', product.id)"
                                                class="mr-4 text-indigo-600 transition duration-150 ease-in-out hover:text-indigo-900"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                @click="deleteProduct(product.id)"
                                                class="text-red-600 transition duration-150 ease-in-out hover:text-red-900"
                                            >
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="products.length === 0">
                                        <td colspan="3" class="py-6 text-center text-gray-500">Nenhum produto encontrado.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
