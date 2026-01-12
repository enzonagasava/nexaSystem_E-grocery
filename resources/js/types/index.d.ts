import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

// Tipos de empresa disponíveis no sistema
export type TipoEmpresa = 'ecommerce' | 'clinica' | 'corretor';

export interface Empresa {
    id: number;
    nome: string;
    tipo: TipoEmpresa;
    tipo_label: string;
}

export interface Auth {
    user: User | null;
    empresa: Empresa | null;
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
    empresa_id: number | null;
    tipo_empresa: TipoEmpresa | null;
    avatar?: string;
    email_verified_at: string | null;
    created_at?: string;
    updated_at?: string;
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

// ============= CLÍNICA =============

export interface Paciente {
    id?: number;
    empresa_id?: number;
    nome: string;
    cpf?: string;
    data_nascimento?: string;
    sexo?: 'masculino' | 'feminino' | 'outro';
    telefone: string;
    email?: string;
    cep?: string;
    endereco?: string;
    numero_endereco?: string;
    bairro?: string;
    cidade?: string;
    estado?: string;
    convenio?: string;
    numero_convenio?: string;
    observacoes?: string;
    created_at_formatted?: string;
    endereco_completo?: string;
    idade?: number | null;
}

export type ConsultaStatus = 'agendada' | 'em-andamento' | 'realizada' | 'cancelada';

export interface Consulta {
    id: number;
    empresa_id: number;
    paciente_id: number;
    paciente?: Paciente;
    data_consulta: string;
    hora_inicio: string;
    hora_fim?: string;
    tipo: string;
    status: ConsultaStatus;
    valor?: number | string;
    motivo?: string;
    observacoes?: string;
    diagnostico?: string;
    prescricao?: string;
    created_at_formatted?: string;
    data_formatada?: string;
    horario_formatado?: string;
}

export interface ConsultaListItem {
    id: number;
    paciente_id: number;
    paciente_nome: string;
    paciente_telefone: string;
    data_consulta: string;
    data_formatada: string;
    hora_inicio: string;
    hora_fim?: string;
    horario_formatado: string;
    tipo: string;
    status: ConsultaStatus;
    valor: string;
    motivo?: string;
    created_at_formatted: string;
}

export type AgendamentoStatus = 'pendente' | 'confirmado' | 'cancelado' | 'realizado';

export interface Agendamento {
    id: number;
    empresa_id: number;
    paciente_id: number;
    paciente?: Paciente;
    data: string;
    hora: string;
    duracao_minutos: number;
    tipo: string;
    status: AgendamentoStatus;
    observacoes?: string;
    created_at_formatted?: string;
    data_formatada?: string;
    hora_formatada?: string;
}

export interface AgendamentoListItem {
    id: number;
    paciente_id: number;
    paciente_nome: string;
    paciente_telefone: string;
    data: string;
    data_formatada: string;
    hora: string;
    hora_formatada: string;
    duracao_minutos: number;
    tipo: string;
    status: AgendamentoStatus;
    observacoes?: string;
    created_at_formatted: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
