export interface LeadForm {
    nome_completo: string,
    email: string,
    contatos: Array<{ 
        numero: string, 
        tipo: string, 
        principal: boolean 
    }>,
    genero: string,
    data_nascimento: string,
    redes_sociais: Array<string>,
    cpf: string,
    rg: string,
    cep: string,
    rua: string,
    bairro: string,
    cidade: string,
    estado: string,
    complemento: string,
    numero: string,
    banco_nome: string,
    banco_codigo: string,
    agencia: string,
    conta: string,
    conta_tipo: string,
    pix: string,
    pix_tipo: string,
    corretor_id: number,
    adicionar_rodizio: boolean,
    status_id: number
    imovel: Array<{
        id: number
        nome: string
    }>
}

export interface LeadListagem{
    id: number,
    nome_completo: string,
    email: string,
    genero: string,
    contatos: Array <{
        numero: string,
        tipo: string,
        principal: boolean
    }>,
    cep: string,
    endereco: string,
    bairro: string,
    cidade: string,
    estado: string,
    complemento: string,
    numero: string,
    corretor_id: number,
    adicionar_rodizio: boolean,
    status_id:  Array<{ 
        id: number, 
        nome: string, 
        ordem: number 
    }>,
    // Valor desejado para propriedade/financiamento (opcional)
    valor_desejado?: number | string
}