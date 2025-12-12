import type { RedesSociaisForm } from './redes-sociais-form';

export interface EmpresaForm {
    nome: string;
    email?: string;
    numero_wpp?: string;
    telefone?: string;
    cnpj?: string;
    endereco?: string;
    cep?: string;
    numero_endereco?: string;
    municipio?: string;
    estado?: string;
    logo?: File | null;
    redes_sociais?: RedesSociaisForm;
}
