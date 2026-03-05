import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, toRaw } from 'vue';

type Imagem = {
    id: string;
    src: string | File;
    previewUrl?: string;
};

export function useProduto() {
    const page = usePage();

    // Coerce incoming page props to safe local types to satisfy TS
    const products = (page.props.products ?? { id: null, nome: '', descricao: '', estoque: 0 }) as any;
    const productTamanhos = (page.props.productTamanhos ?? []) as any[];
    const imagensExistentes = ref<Imagem[]>([]);
    const imagensFiles = ref<Imagem[]>([]);
    const urlMap = new Map<string, string>();
    const imagemModal = ref<string | undefined>(undefined);

    function carregarImagensExistentes(urls: string[]) {
        imagensExistentes.value = urls.map((url, i) => ({
            id: i.toString(),
            src: url,
        }));
    }

    onMounted(() => {
        if (Array.isArray(page.props.imagemPaths)) {
            carregarImagensExistentes(page.props.imagemPaths);
        }
    });

    function adicionarTamanho() {
        productTamanhos.push({ nome: '', preco: 0 });
    }

    function removerTamanho(index: number) {
        productTamanhos.splice(index, 1);
    }

    function onFilesChange(event: Event) {
        const target = event.target as HTMLInputElement;
        if (target.files) {
            const selectedFiles = Array.from(target.files);
            for (const file of selectedFiles) {
                const id = 'novo-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                const blobUrl = URL.createObjectURL(file);
                imagensFiles.value.push({ id, src: file, previewUrl: blobUrl });
            }
        }
    }

    const imagensParaRenderizar = computed(() => {
        const imagensExistentesRaw = toRaw(imagensExistentes.value);
        const existentes = imagensExistentesRaw.map((imagem) => {
            let url = '';
            if (typeof imagem.src === 'string') {
                url = imagem.src;
            } else if (imagem.src && typeof (imagem.src as any).url === 'string') {
                url = (imagem.src as any).url;
            }
            return { id: imagem.id, url };
        });

        const novos = imagensFiles.value.map((imagem) => {
            if (typeof imagem.src === 'string') {
                return { id: imagem.id, url: imagem.src };
            } else {
                const objectUrl = URL.createObjectURL(imagem.src as File);
                urlMap.set(imagem.id, objectUrl);
                return { id: imagem.id, url: objectUrl };
            }
        });

        return [...existentes, ...novos];
    });

    function removerImagem(id: string) {
        const indexExistente = imagensExistentes.value.findIndex((img) => img.id === id);
        if (indexExistente !== -1) {
            imagensExistentes.value.splice(indexExistente, 1);
            imagensExistentes.value = [...imagensExistentes.value];
            return;
        }

        const indexNovo = imagensFiles.value.findIndex((img) => img.id === id);
        if (indexNovo !== -1) {
            const urlCriada = urlMap.get(id);
            if (urlCriada) {
                URL.revokeObjectURL(urlCriada);
                urlMap.delete(id);
            }
            imagensFiles.value.splice(indexNovo, 1);
        }
    }

    function abrirModal(id: string) {
        const imagem = imagensParaRenderizar.value.find((img) => img.id === id);
        if (imagem) {
            imagemModal.value = imagem.url;
        }
    }

    function fecharModal() {
        imagemModal.value = undefined;
    }

    function handleSubmit() {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('nome', products.nome);
        formData.append('descricao', products.descricao);
        formData.append('estoque', products.estoque.toString());
        formData.append('tamanhos', JSON.stringify(productTamanhos));

        const imagensExistentesIds = imagensExistentes.value.map((img) => {
            const rawSrc = toRaw(img.src);
            return rawSrc; // aqui pode ser só o URL, pois id não parece estar no src
        });

        formData.append('imagensExistentes', JSON.stringify(imagensExistentesIds));

        imagensFiles.value.forEach((imagemFile, index) => {
            formData.append('imagensNovas[]', imagemFile.src as File, `imagem${index}.png`);
        });

        Inertia.post(route('admin.ecommerce.produtos.update', products.id), formData, {
            forceFormData: true,
            onSuccess: () => {
                alert('Produto salvo com sucesso!');
                // Resetar formulário (ajuste conforme sua necessidade)
                products.nome = '';
                products.descricao = '';
                productTamanhos.splice(0, productTamanhos.length);
                products.estoque = 0;
                imagensFiles.value = [];
                imagensExistentes.value = [];
            },
            onError: (errors) => {
                alert('Erro ao salvar produto');
                console.error(errors);
            },
        });
    }

    return {
        products,
        productTamanhos,
        imagensExistentes,
        imagensFiles,
        imagensParaRenderizar,
        imagemModal,
        adicionarTamanho,
        removerTamanho,
        onFilesChange,
        removerImagem,
        abrirModal,
        fecharModal,
        handleSubmit,
    };
}
