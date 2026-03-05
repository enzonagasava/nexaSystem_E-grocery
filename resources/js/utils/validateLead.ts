// Utilitário de validação para cadastro de lead
// Todas as mensagens e regras em pt-BR, conforme padrão do projeto

export function validateLead(formRef: any, step: string) {
  const errors: Record<string, string> = {};

  // Step 0: Informações Pessoais
  if (step === 'StepInformacaoPessoal') {
    if (!formRef.nome_completo || !String(formRef.nome_completo).trim()) {
      errors.nome_completo = 'O nome completo é obrigatório.';
    } else if (formRef.nome_completo.trim().length < 3) {
      errors.nome_completo = 'O nome deve ter pelo menos 3 caracteres.';
    }

    if (formRef.email && formRef.email.trim() !== '') {
      // Verifica se tem @
      if (!formRef.email.includes('@')) {
          errors.email = 'O email deve conter o símbolo @.';
      }
    }

    if (formRef.data_nascimento) {
      const dataNasc = new Date(formRef.data_nascimento);
      const hoje = new Date();
      if (dataNasc > hoje) {
        errors.data_nascimento = 'Data de nascimento não pode ser futura.';
      } else if (dataNasc.getFullYear() < 1900) {
        errors.data_nascimento = 'Data de nascimento inválida.';
      }
    }

    // Validação de CPF
    if (formRef.cpf && formRef.cpf.trim()) {
      const cpfLimpo = formRef.cpf.replace(/\D/g, '');
      if (cpfLimpo.length !== 11) {
        errors.cpf = 'CPF deve ter 11 dígitos.';
      }
    }

    // Validação de RG (se informado)
    if (formRef.rg && formRef.rg.trim().length < 5) {
      errors.rg = 'RG deve ter pelo menos 5 caracteres.';
    }

    // Validação de contatos
    if (!formRef.contatos || !Array.isArray(formRef.contatos) || formRef.contatos.length === 0) {
      errors.contatos = 'Adicione pelo menos um contato.';
    } else {
      const contatosValidos = formRef.contatos.filter((contato: any) => {
        return contato.numero && contato.numero.trim().replace(/\D/g, '').length >= 10;
      });
      
      if (contatosValidos.length === 0) {
        errors.contatos = 'Pelo menos um contato válido é obrigatório.';
      }
    }

    // Validação de redes sociais (se houver)
    if (formRef.redes_sociais && Array.isArray(formRef.redes_sociais)) {
      formRef.redes_sociais.forEach((rede: any, index: number) => {
        if (rede.plataforma && rede.url) {
          if (!rede.url.match(/^https?:\/\//)) {
            errors[`redes_sociais_${index}_url`] = 'URL deve começar com http:// ou https://';
          }
        }
      });
    }
  }

  // Step 1: Endereço
  if (step === 'StepLocalizacao') {
    const camposLocalizacao = ['endereco', 'bairro', 'cidade', 'estado', 'cep', 'complemento'];
    
    // Verifica se algum campo de endereço foi preenchido
    const algumPreenchido = camposLocalizacao.some(campo => 
      formRef[campo] !== undefined && 
      formRef[campo] !== null && 
      String(formRef[campo]).trim() !== ''
    );
    
    // Se algum campo foi preenchido, todos os obrigatórios devem ser validados
    if (algumPreenchido) {
      // Limpa erros anteriores desses campos
      camposLocalizacao.forEach(campo => {
        if (errors[campo]) delete errors[campo];
      });
      
      // Validação do CEP
      if (!formRef.cep || !String(formRef.cep).trim()) {
        errors.cep = 'CEP é obrigatório.';
      } else if (!/^\d{5}-?\d{3}$/.test(formRef.cep.replace(/\D/g, ''))) {
        errors.cep = 'Informe um CEP válido (formato: 00000-000 ou 00000000).';
      }
      
      // Validação do Endereço
      if (!formRef.rua || !String(formRef.rua).trim()) {
        errors.rua = 'Endereço é obrigatório.';
      } else if (formRef.rua.trim().length < 3) {
        errors.rua = 'Endereço deve ter pelo menos 3 caracteres.';
      }
      
      // Validação do Bairro
      if (!formRef.bairro || !String(formRef.bairro).trim()) {
        errors.bairro = 'Bairro é obrigatório.';
      } else if (formRef.bairro.trim().length < 2) {
        errors.bairro = 'Bairro deve ter pelo menos 2 caracteres.';
      }
      
      // Validação da Cidade
      if (!formRef.cidade || !String(formRef.cidade).trim()) {
        errors.cidade = 'Cidade é obrigatória.';
      } else if (formRef.cidade.trim().length < 2) {
        errors.cidade = 'Cidade deve ter pelo menos 2 caracteres.';
      }
      
      // Validação do Estado
      if (!formRef.estado || !String(formRef.estado).trim()) {
        errors.estado = 'Estado é obrigatório.';
      } else if (!/^[A-Z]{2}$/.test(formRef.estado.toUpperCase())) {
        errors.estado = 'Estado deve ter 2 letras maiúsculas (ex: SP, RJ).';
      } else {
        // Formata para maiúsculas
        formRef.estado = formRef.estado.toUpperCase();
      }
      
      // Validação do Complemento (se preenchido)
      if (formRef.complemento && formRef.complemento.trim()) {
        if (formRef.complemento.trim().length > 255) {
          errors.complemento = 'Complemento deve ter no máximo 255 caracteres.';
        }
      }
    } else {
      // Se NENHUM campo foi preenchido, limpa todos os erros
      camposLocalizacao.forEach(campo => {
        if (errors[campo]) delete errors[campo];
      });
    }
  }

  // Step 2: Dados Bancários
  if (step === 'StepBanco') {
    // Se algum campo bancário for preenchido, validar todos os obrigatórios
    const camposBancarios = ['banco_codigo', 'banco_nome', 'agencia', 'conta', 'conta_tipo'];
    const algumPreenchido = camposBancarios.some(campo => 
      formRef[campo] && String(formRef[campo]).trim()
    );

    if (algumPreenchido) {
      if (!formRef.banco_codigo || !String(formRef.banco_codigo).trim()) {
        errors.banco_codigo = 'Código do banco é obrigatório.';
      }

      if (!formRef.banco_nome || !String(formRef.banco_nome).trim()) {
        errors.banco_nome = 'Nome do banco é obrigatório.';
      }

      if (!formRef.agencia || !String(formRef.agencia).trim()) {
        errors.agencia = 'Agência é obrigatória.';
      } else if (!/^\d{1,10}$/.test(formRef.agencia.replace(/\D/g, ''))) {
        errors.agencia = 'Agência deve conter apenas números (até 10 dígitos).';
      }

      if (!formRef.conta || !String(formRef.conta).trim()) {
        errors.conta = 'Conta é obrigatória.';
      }

      if (!formRef.conta_tipo || !['corrente', 'poupanca'].includes(formRef.conta_tipo)) {
        errors.conta_tipo = 'Selecione o tipo de conta.';
      }
    }

    // Validação de PIX
    if (formRef.pix_tipo && formRef.pix_tipo.trim()) {
      if (!formRef.pix || !String(formRef.pix).trim()) {
        errors.pix = 'Chave PIX é obrigatória quando o tipo é selecionado.';
      } else {
        // Validação específica por tipo de PIX
        switch (formRef.pix_tipo) {
          case 'cpf':
            const cpfLimpo = formRef.pix.replace(/\D/g, '');
            if (cpfLimpo.length !== 11) {
              errors.pix = 'Informe um CPF válido para a chave PIX.';
            }
            break;
          case 'celular':
            const celularLimpo = formRef.pix.replace(/\D/g, '');
            if (celularLimpo.length < 10 || celularLimpo.length > 11) {
              errors.pix = 'Informe um número de celular válido (10 ou 11 dígitos).';
            }
            break;
          case 'email':
            if (!/^\S+@\S+\.\S+$/.test(formRef.pix)) {
              errors.pix = 'Informe um email válido para a chave PIX.';
            }
            break;
          case 'aleatorio':
            if (formRef.pix.trim().length < 32 || formRef.pix.trim().length > 36) {
              errors.pix = 'Chave aleatória deve ter entre 32 e 36 caracteres.';
            }
            break;
        }
      }
    } else if (formRef.pix && formRef.pix.trim()) {
      // Se tem PIX mas não tem tipo
      errors.pix_tipo = 'Selecione o tipo da chave PIX.';
    }
  }

  // Step 3: Gerenciamento
  if (step === 'StepGerenciamento') {
    if (!formRef.status_id) {
      errors.status = 'Selecione um status válido para o lead.';
    }

    // Validação do corretor (se informado)
    if (formRef.corretor_id && (isNaN(Number(formRef.corretor_id)) || Number(formRef.corretor_id) <= 0)) {
      errors.corretor_id = 'Corretor inválido.';
    }

    // Se não está no rodízio, deve ter um corretor atribuído
    if (formRef.adicionar_rodizio === false && !formRef.corretor_id) {
      errors.corretor_id = 'Para leads fora do rodízio, é necessário atribuir um corretor.';
    }
  }

  return errors;
}