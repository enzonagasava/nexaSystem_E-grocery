<script setup lang="ts">
import ImageUploader from '@/components/admin/ImageUploader.vue';
import TamanhosEditor from '@/components/admin/TamanhosEditor.vue';
import { useProduto } from '@/composables/useEditarProduto';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';

const {
    products,
    productTamanhos,
    imagensParaRenderizar,
    imagemModal,
    adicionarTamanho,
    removerTamanho,
    onFilesChange,
    removerImagem,
    abrirModal,
    fecharModal,
    handleSubmit,
} = useProduto();
</script>

<template>
    <Head>
        <title>Gerenciar Produtos - Família Mogi</title>
        <meta name="description" content="Gerenciamento de produtos da Família Mogi" />
    </Head>
    <AuthLayout>
        <div class="flex min-h-screen bg-gray-100">
            <!-- Main Content -->
            <main class="flex-1 p-10">
                <div class="mx-auto max-w-4xl rounded bg-white p-8 shadow">
                    <h2 class="mb-6 text-2xl font-bold">Adicionar Novo Produto</h2>
                    <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
                        <div>
                            <label for="nome" class="mb-2 block font-semibold text-gray-700">Nome do Produto</label>
                            <input
                                id="nome"
                                v-model="products.nome"
                                type="text"
                                placeholder="Digite o nome do produto"
                                class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                required
                            />
                        </div>

                        <!-- Campo Descrição do Produto -->
                        <div>
                            <label for="descricao" class="mb-2 block font-semibold text-gray-700">Descrição do Produto</label>
                            <textarea
                                id="descricao"
                                v-model="products.descricao"
                                rows="4"
                                placeholder="Digite a descrição do produto"
                                class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                required
                            ></textarea>
                        </div>

                        <TamanhosEditor :tamanhos="productTamanhos" @adicionar="adicionarTamanho" @remover="removerTamanho" />

                        <div>
                            <label for="estoque" class="mb-2 block font-semibold text-gray-700">Quantidade em Estoque</label>
                            <input
                                id="estoque"
                                v-model.number="products.estoque"
                                type="number"
                                min="0"
                                placeholder="Quantidade disponível"
                                class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                required
                            />
                        </div>

                        <ImageUploader
                            :imagensParaRenderizar="imagensParaRenderizar"
                            :imagemModal="imagemModal"
                            @onFilesChange="onFilesChange"
                            @removerImagem="removerImagem"
                            @abrirModal="abrirModal"
                            @fecharModal="fecharModal"
                        />

                        <button type="submit" class="rounded bg-green-600 px-6 py-2 font-semibold text-white transition hover:bg-green-700">
                            Salvar Produto
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </AuthLayout>
</template>
