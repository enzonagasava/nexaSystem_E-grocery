// Utilitário de validação para cadastro de imóvel
// Todas as mensagens e regras em pt-BR, conforme padrão do projeto

export function validateImovel(formRef: any, step: string, anoAtual: number = new Date().getFullYear()) {
  const errors: Record<string, string> = {};

  // StepZero
  if (step === 'StepZero') {
    if (!formRef.modalidade) {
      errors.modalidade = 'Selecione a modalidade do imóvel (venda ou locação).';
    }
  }

  // StepIdentificacao
  if (step === 'StepIdentificacao') {
    if (!formRef.nome || !String(formRef.nome).trim()) {
      errors.nome = 'O nome do imóvel é obrigatório.';
    }
    if (!formRef.codigo || !String(formRef.codigo).trim()) {
      errors.codigo = 'O código interno é obrigatório.';
    }
    if (!['ativo','inativo','vendido','reservado'].includes(formRef.status)) {
      errors.status = 'Selecione um status válido.';
    }
    if (!['apartamento','casa','terreno','comercial'].includes(formRef.categoria)) {
      errors.categoria = 'Selecione uma categoria de imóvel válida.';
    }
    if (formRef.exclusividade !== 0 && formRef.exclusividade !== 1) {
      errors.exclusividade = 'Informe se o imóvel é exclusivo.';
    }
    if (!['novo','usado','lancamento'].includes(formRef.condicao)) {
      errors.condicao = 'Selecione a condição do imóvel.';
    }
  }

  // StepLocalizacao
  if (step === 'StepLocalizacao') {
    if (!formRef.cep || !/^\d{5}-?\d{3}$/.test(formRef.cep)) {
      errors.cep = 'Informe um CEP válido.';
    }
    ['estado','cidade','bairro','endereco','numero'].forEach(campo => {
      if (!formRef[campo] || !String(formRef[campo]).trim()) {
        errors[campo] = `O campo ${campo} é obrigatório.`;
      }
    });
    if (formRef.mostrar_endereco_completo !== 0 && formRef.mostrar_endereco_completo !== 1) {
      errors.mostrar_endereco_completo = 'Informe se o endereço completo será exibido.';
    }
  }

  // StepCaracteristicas
  if (step === 'StepCaracteristicas') {
    if (['casa','apartamento'].includes(formRef.categoria)) {
      if (!Number.isInteger(formRef.dormitorios) || formRef.dormitorios < 0) {
        errors.dormitorios = 'Informe a quantidade de quartos (mínimo 0).';
      }
      if (!Number.isInteger(formRef.suites) || formRef.suites < 0) {
        errors.suites = 'Informe a quantidade de suítes (mínimo 0).';
      }
      if (!Number.isInteger(formRef.banheiros) || formRef.banheiros < 0) {
        errors.banheiros = 'Informe a quantidade de banheiros (mínimo 0).';
      }
      if (!Number.isInteger(formRef.vagas) || formRef.vagas < 0) {
        errors.vagas = 'Informe a quantidade de vagas (mínimo 0).';
      }
      if (!formRef.area_construida || formRef.area_construida <= 0) {
        errors.area_construida = 'Informe a área construída (maior que 0).';
      }
    }
    if (formRef.categoria === 'comercial') {
      if (!Number.isInteger(formRef.salas) || formRef.salas < 0) {
        errors.salas = 'Informe a quantidade de salas (mínimo 0).';
      }
      if (!Number.isInteger(formRef.banheiros) || formRef.banheiros < 0) {
        errors.banheiros = 'Informe a quantidade de banheiros (mínimo 0).';
      }
      if (!Number.isInteger(formRef.vagas) || formRef.vagas < 0) {
        errors.vagas = 'Informe a quantidade de vagas (mínimo 0).';
      }
      if (!formRef.area_construida || formRef.area_construida <= 0) {
        errors.area_construida = 'Informe a área construída (maior que 0).';
      }
    }
    if (formRef.categoria === 'terreno') {
      if (!formRef.area_terreno_largura || formRef.area_terreno_largura <= 0) {
        errors.area_terreno_largura = 'Informe a largura do terreno (maior que 0).';
      }
      if (!formRef.area_terreno_comprimento || formRef.area_terreno_comprimento <= 0) {
        errors.area_terreno_comprimento = 'Informe o comprimento do terreno (maior que 0).';
      }
    }
    if (!formRef.area_util || formRef.area_util <= 0) {
      errors.area_util = 'Informe a área útil (maior que 0).';
    }
    if (!formRef.area_total || formRef.area_total <= 0) {
      errors.area_total = 'Informe a área total (maior que 0).';
    }
    if (!formRef.ano_construcao || !Number.isInteger(formRef.ano_construcao) || formRef.ano_construcao < 1800 || formRef.ano_construcao > anoAtual) {
      errors.ano_construcao = `Ano de construção inválido. Deve ser entre 1800 e ${anoAtual}.`;
    }
  }

  // StepValor
  if (step === 'StepValor') {
    if (!formRef.valor_venda || formRef.valor_venda <= 0) {
      errors.valor_venda = 'Informe o valor de venda (maior que 0).';
    }
    if (formRef.categoria === 'apartamento' && (formRef.valor_condominio === undefined || formRef.valor_condominio < 0)) {
      errors.valor_condominio = 'Informe o valor do condomínio (zero ou maior).';
    }
    if (formRef.valor_iptu === undefined || formRef.valor_iptu < 0) {
      errors.valor_iptu = 'Informe o valor do IPTU (zero ou maior).';
    }
    if (formRef.aceita_financiamento !== 0 && formRef.aceita_financiamento !== 1) {
      errors.aceita_financiamento = 'Informe se aceita financiamento.';
    }
    if (formRef.aceita_permuta !== 0 && formRef.aceita_permuta !== 1) {
      errors.aceita_permuta = 'Informe se aceita permuta.';
    }
    if (formRef.comissao_percent === undefined || formRef.comissao_percent < 0) {
      errors.comissao_percent = 'Informe a comissão (zero ou maior).';
    }
  }

  // StepProprietario
  if (step === 'StepProprietario') {
    if (!formRef.proprietario_nome || !String(formRef.proprietario_nome).trim()) {
      errors.proprietario_nome = 'O nome do proprietário é obrigatório.';
    }
    // Remove formatting from phone to validate
    const telDigitsOnly = (formRef.proprietario_telefone || '').replace(/\D/g, '');
    if (!telDigitsOnly || !/^\d{10,11}$/.test(telDigitsOnly)) {
      errors.proprietario_telefone = 'Informe um telefone válido (10 ou 11 dígitos).';
    }
    if (!formRef.proprietario_email || !/^\S+@\S+\.\S+$/.test(formRef.proprietario_email)) {
      errors.proprietario_email = 'Informe um e-mail válido.';
    }
    // Remove formatting from documento (CPF/CNPJ) to validate
    const docDigitsOnly = (formRef.proprietario_documento || '').replace(/\D/g, '');
    if (!docDigitsOnly || (!/^\d{11}$/.test(docDigitsOnly) && !/^\d{14}$/.test(docDigitsOnly))) {
      errors.proprietario_documento = 'Informe um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.';
    }
    // Autorização: depende do fluxo, pode ser validado no envio final
  }

  // StepDetalhes
  if (step === 'StepDetalhes') {
    if (!formRef.descricao || !String(formRef.descricao).trim()) {
      errors.descricao = 'A descrição do imóvel é obrigatória.';
    }
    if (!['mobiliado','semi','nao',''].includes(formRef.mobilia)) {
      errors.mobilia = 'Selecione o tipo de mobília.';
    }
    if (formRef.varanda !== 0 && formRef.varanda !== 1) {
      errors.varanda = 'Informe se possui varanda.';
    }
  }

  // StepMidia
  if (step === 'StepMidia') {
    // Validation is now optional — files are managed by StepMidia component
    // The parent passes mediaFiles which contains imagens/videos/plantas arrays
    const imgs = formRef.mediaImagens
    if (!imgs || !Array.isArray(imgs) || imgs.length === 0) {
      // Also check legacy previews for backwards compat
      if (!formRef.previews || !Array.isArray(formRef.previews) || formRef.previews.length === 0) {
        errors.imagens = 'Adicione pelo menos uma foto do imóvel.';
      }
    }
  }

  return errors;
}
