// resources/js/types/forms/contato-form.d.ts

export interface Contato {
  id?: number;
  nome_completo: string;
  email?: string | null;
  contatos?: ContatoTelefone[];
  genero?: string | null;
  data_nascimento?: string | null;
  
  // Documentos
  cpf?: string | null;
  rg?: string | null;
  cnh?: string | null;
  
  // Endereço
  cep?: string | null;
  rua?: string | null;
  bairro?: string | null;
  cidade?: string | null;
  estado?: string | null;
  complemento?: string | null;
  numero?: string | null;
  
  // Profissional
  profissao?: string | null;
  empresa?: string | null;
  renda_mensal?: number | null;
  
  // Conta Bancária
  banco_nome?: string | null;
  banco_codigo?: string | null;
  agencia?: string | null;
  conta?: string | null;
  conta_tipo?: string | null;
  pix?: string | null;
  pix_tipo?: string | null;
  
  // Relacionamentos
  corretor_id?: number | null;
  corretor?: {
    id: number;
    nome: string;
  } | null;
  
  // Status e classificação
  status: string;
  tipo_relacao?: string | null;
  nivel_interesse?: number | null;
  ultimo_contato?: string | null;
  
  // Preferências
  preferencias_imoveis?: any[] | null;
  observacoes?: string | null;
  
  // Controle
  adicionar_rodizio?: boolean;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
}

export interface ContatoTelefone {
  numero: string;
  tipo: 'WhatsApp' | 'Celular' | 'Telefone' | 'Comercial';
  principal: boolean;
}

export interface ContatoListagem {
  id: number;
  nome_completo: string;
  email?: string | null;
  contatos?: ContatoTelefone[];
  status: string;
  tipo_relacao?: string | null;
  nivel_interesse?: number | null;
  ultimo_contato?: string | null;
  corretor_id?: number | null;
  corretor?: {
    id: number;
    nome: string;
  } | null;
  cidade?: string | null;
  estado?: string | null;
  profissao?: string | null;
  empresa?: string | null;
  renda_mensal?: number | null;
  data_nascimento?: string | null;
  cpf?: string | null;
  created_at?: string;
  adicionar_rodizio?: boolean;
}

export interface ContatoForm {
  nome_completo: string;
  email?: string | null;
  contatos: ContatoTelefone[];
  genero?: string | null;
  data_nascimento?: string | null;
  
  cpf?: string | null;
  rg?: string | null;
  cnh?: string | null;
  
  cep?: string | null;
  rua?: string | null;
  bairro?: string | null;
  cidade?: string | null;
  estado?: string | null;
  complemento?: string | null;
  numero?: string | null;
  
  profissao?: string | null;
  empresa?: string | null;
  renda_mensal?: number | null;
  
  banco_nome?: string | null;
  banco_codigo?: string | null;
  agencia?: string | null;
  conta?: string | null;
  conta_tipo?: string | null;
  pix?: string | null;
  pix_tipo?: string | null;
  
  corretor_id?: number | null;
  status: string;
  tipo_relacao?: string | null;
  nivel_interesse?: number | null;
  
  preferencias_imoveis?: any[] | null;
  observacoes?: string | null;
}