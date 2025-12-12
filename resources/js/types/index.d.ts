import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    numero: string;
    cargo_id: number;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface ProdutoTamanhoPivot {
    preco: number;
}

export interface ProdutoTamanho {
    id: number;
    nome: string;
    pivot: ProdutoTamanhoPivot;
}

export interface ProdutoImagem {
    id: number;
    produto_id: number;
    imagem_path: string;
    ordem: number;
    imagem_url: string;
}

export interface Produto {
    id: number;
    nome: string;
    descricao: string;
    estoque: number;
    tamanhos: ProdutoTamanho[];
    imagens: ProdutoImagem[];
}

export interface ProdutoSelecionado extends Produto {
    quantidade: number;
    valor_unitario: number;
    valor: number;
}

export interface Cliente {
    id?: number;
    nome: string;
    numero: string;
    email: string;
    cep: string;
    endereco: string;
    numero_endereco: string;
    municipio: string;
    estado: string;
}

export interface Pedido {
    id: number;
    cod_pedido: string;
    cliente: string;
    endereco: string;
    plataforma: string;
    produtos: string[];
    valor: string;
    status: string;
    created_at_formatted: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
