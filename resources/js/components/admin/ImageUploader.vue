<script setup lang="ts">
defineProps({
    imagensParaRenderizar: Array,
    imagemModal: String,
});

defineEmits(['onFilesChange', 'removerImagem', 'abrirModal', 'fecharModal']);
</script>

<template>
    <div>
        <label class="mb-2 block font-semibold text-gray-700">Imagem do Produto</label>
        <input
            type="file"
            multiple
            accept="image/*"
            @change="$emit('onFilesChange', $event)"
            class="block w-full cursor-pointer text-sm text-gray-600 file:mr-4 file:rounded file:border-0 file:bg-green-600 file:px-4 file:py-2 file:text-sm file:text-white hover:file:bg-green-700"
        />
        <p class="mt-1 text-gray-600">
            {{ imagensParaRenderizar.length }} arquivo{{ imagensParaRenderizar.length !== 1 ? 's' : '' }} selecionado{{
                imagensParaRenderizar.length !== 1 ? 's' : ''
            }}.
        </p>
        <div class="mt-4 flex flex-wrap gap-4">
            <div
                v-for="imagem in imagensParaRenderizar"
                :key="imagem.id"
                class="relative h-24 w-24 cursor-pointer overflow-hidden rounded border border-gray-300 shadow"
            >
                <img :src="imagem.url" alt="Preview da Imagem" class="h-full w-full object-cover" @click="$emit('abrirModal', imagem.id)" />
                <a
                    @click.stop="$emit('removerImagem', imagem.id)"
                    class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs text-white hover:bg-red-800"
                    title="Remover imagem"
                >
                    ×
                </a>
            </div>
        </div>

        <div v-if="imagemModal" @click.self="$emit('fecharModal')" class="bg-opacity-70 fixed inset-0 z-50 flex items-center justify-center bg-black">
            <div class="relative max-h-[80vh] max-w-3xl">
                <button
                    @click="$emit('fecharModal')"
                    class="bg-opacity-50 hover:bg-opacity-80 absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-black text-xl font-bold text-white"
                    title="Fechar"
                >
                    ×
                </button>
                <img :src="imagemModal" alt="Imagem ampliada" class="max-h-[80vh] max-w-full rounded" />
            </div>
        </div>
    </div>
</template>
