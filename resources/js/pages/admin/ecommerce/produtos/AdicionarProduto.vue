<script setup lang="ts">
import { useMoneyConfig } from '@/composables/useMoneyConfig';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const moneyConfig = useMoneyConfig();
const produto = reactive({
    nome: '',
    descricao: '',
    tamanhos: [{ nome: '', preco: 0 }],
    estoque: 0,
    imagemFile: null as File | null,
});

const previewImagens = ref<string[]>([]);
const imagemModal = ref<string | null>(null);
const imagensFiles = ref<File[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);

function adicionarTamanho() {
    produto.tamanhos.push({ nome: '', preco: 0 });
}

function removerTamanho(index: number) {
    produto.tamanhos.splice(index, 1);
}

function onFilesChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const selectedFiles = Array.from(target.files);
        const allFiles = [...imagensFiles.value];
        for (const file of selectedFiles) {
            if (!allFiles.some((f) => f.name === file.name && f.size === file.size)) {
                allFiles.push(file);
            }
        }
        imagensFiles.value = allFiles;
        previewImagens.value = imagensFiles.value.map((file) => URL.createObjectURL(file));
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    } else {
        imagensFiles.value = [];
        previewImagens.value = [];
    }
}

function removerImagem(index: number) {
    URL.revokeObjectURL(previewImagens.value[index]);
    previewImagens.value.splice(index, 1);
    imagensFiles.value.splice(index, 1);
}

function abrirModal(index: number) {
    imagemModal.value = previewImagens.value[index];
}

function fecharModal() {
    imagemModal.value = null;
}

function handleSubmit() {
    const formData = new FormData();
    formData.append('nome', produto.nome);
    formData.append('descricao', produto.descricao);
    formData.append('estoque', produto.estoque.toString());
    produto.tamanhos.forEach((tamanho, index) => {
        formData.append(`tamanhos[${index}][nome]`, tamanho.nome);
        formData.append(`tamanhos[${index}][preco]`, tamanho.preco.toString());
    });
    imagensFiles.value.forEach((file) => {
        formData.append('imagens[]', file);
    });
    router.post(route('admin.ecommerce.produtos.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            produto.nome = '';
            produto.descricao = '';
            produto.tamanhos = [{ nome: '', preco: 0 }];
            produto.estoque = 0;
            produto.imagemFile = null;
        },
    });
}
</script>

<template>
    <Head>
        <title>Adicionar Produto</title>
        <meta name="description" content="Adicionar novo produto" />
    </Head>

    <AuthLayout>
        <div class="flex min-h-screen ">
            <main class="flex-1 p-10">
                <div class="mx-auto max-w-4xl rounded p-8 shadow">
                    <h2 class="mb-6 text-2xl font-bold">Adicionar Novo Produto</h2>

                    <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
                        <div>
                            <label for="nome" class="mb-2 block font-semibold text-gray-700">Nome do Produto</label>
                            <input
                                id="nome"
                                v-model="produto.nome"
                                type="text"
                                placeholder="Digite o nome do produto"
                                class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                required
                            />
                        </div>

                        <div>
                            <label for="descricao" class="mb-2 block font-semibold text-gray-700">Descrição do Produto</label>
                            <textarea
                                id="descricao"
                                v-model="produto.descricao"
                                rows="4"
                                placeholder="Digite a descrição do produto"
                                class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                required
                            ></textarea>
                        </div>

                        <div>
                            <label class="mb-2 block font-semibold text-gray-700">Tamanhos e Preços</label>
                            <div v-for="(tamanho, index) in produto.tamanhos" :key="index" class="mb-3 flex items-center gap-4">
                                <input
                                    v-model="tamanho.nome"
                                    type="text"
                                    placeholder="Ex: 200g, 1kg"
                                    class="flex-1 rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                                <input
                                    v-model.lazy="tamanho.preco"
                                    v-money3="moneyConfig"
                                    placeholder="Preço"
                                    class="w-32 rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="removerTamanho(index)"
                                    class="rounded border border-red-600 px-2 py-1 font-bold text-red-600 hover:text-red-800"
                                    title="Remover tamanho"
                                >
                                    &times;
                                </button>
                            </div>
                            <button
                                type="button"
                                @click="adicionarTamanho"
                                class="mt-2 rounded bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
                            >
                                + Adicionar Tamanho
                            </button>
                        </div>

                        <div>
                            <label for="estoque" class="mb-2 block font-semibold text-gray-700">Quantidade em Estoque</label>
                            <input
                                id="estoque"
                                v-model.number="produto.estoque"
                                type="number"
                                min="0"
                                placeholder="Quantidade disponível"
                                class="w-full rounded border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none"
                                required
                            />
                        </div>

                        <div>
                            <label class="mb-2 block font-semibold text-gray-700">Imagem do Produto</label>
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept="image/*"
                                @change="onFilesChange"
                                class="block w-full cursor-pointer text-sm text-gray-600 file:mr-4 file:rounded file:border-0 file:bg-green-600 file:px-4 file:py-2 file:text-sm file:text-white hover:file:bg-green-700"
                            />
                            <p class="mt-1 text-gray-600">
                                {{ imagensFiles.length }} arquivo{{ imagensFiles.length !== 1 ? 's' : '' }} selecionado{{ imagensFiles.length !== 1 ? 's' : '' }}.
                            </p>
                            <div v-if="previewImagens.length" class="mt-4 flex flex-wrap gap-4">
                                <div
                                    v-for="(src, index) in previewImagens"
                                    :key="index"
                                    class="relative h-24 w-24 cursor-pointer overflow-hidden rounded border border-gray-300 shadow"
                                >
                                    <img :src="src" alt="Preview da Imagem" class="h-full w-full object-cover" @click="abrirModal(index)" />
                                    <a
                                        href="#"
                                        @click.prevent="removerImagem(index)"
                                        class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs text-white hover:bg-red-800"
                                        title="Remover imagem"
                                    >
                                        ×
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="rounded bg-green-600 px-6 py-2 font-semibold text-white transition hover:bg-green-700">
                                Salvar Produto
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>

        <div v-if="imagemModal" @click.self="fecharModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
            <div class="relative max-h-[80vh] max-w-3xl">
                <button
                    type="button"
                    @click="fecharModal"
                    class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-black bg-opacity-50 text-xl font-bold text-white hover:bg-opacity-80"
                    title="Fechar"
                >
                    ×
                </button>
                <img :src="imagemModal" alt="Imagem ampliada" class="max-h-[80vh] max-w-full rounded" />
            </div>
        </div>
    </AuthLayout>
</template>
