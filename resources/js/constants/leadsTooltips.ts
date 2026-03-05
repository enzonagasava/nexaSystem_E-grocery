/**
 * Tooltips instrutivos para o formulário de cadastro de leads
 * 
 * Cada chave representa um campo do formulário e contém texto explicativo
 * para auxiliar o usuário no preenchimento correto das informações.
 */
export const leadsTooltips = {
  // StepInformacaoPessoal - Informações Pessoais
  nome_completo: 'Nome completo do lead. Este campo é obrigatório e deve conter nome e sobrenome.',
  email: 'E-mail principal para comunicação com o lead.',
  genero: 'Gênero do lead. Selecione uma opção para personalizar a comunicação. Opções: Masculino, Feminino, Outro ou Prefiro não informar.',
  data_nascimento: 'Data de nascimento do lead. Utilizada para cálculo de idade, envio de mensagens personalizadas e segmentação de público.',
  cpf: 'Cadastro de Pessoa Física (CPF) para identificação única. Digite apenas números ou utilize o formato 000.000.000-00.',
  rg: 'Registro Geral (RG) - número do documento de identidade. Inclua o número e o órgão emissor se necessário.',
  cnh: 'Número da Carteira Nacional de Habilitação. Campo opcional para leads que podem ter interesse em financiamento.',
  
  // Contatos
  contatos: 'Adicione múltiplos números de contato. Clique em "+ Adicionar Contato" para incluir telefones fixos, celulares, WhatsApp ou telefones comerciais.',
  'contatos.numero': 'Número de telefone com DDD. Para celulares: (11) 99999-9999. Para fixos: (11) 3333-3333.',
  'contatos.tipo': 'Tipo do número: Celular, Telefone Fixo, WhatsApp ou Comercial. WhatsApp será usado para comunicação via mensagem instantânea.',
  'contatos.principal': 'Marque este campo para definir o número principal de contato. O sistema priorizará este número nas comunicações.',
  
  // Redes Sociais
  redes_sociais: 'Perfis do lead em redes sociais. Útil para conhecer melhor o perfil do lead e estabelecer contato por outros canais.',
  'redes_sociais.plataforma': 'Selecione a plataforma da rede social: Facebook, Instagram, LinkedIn, Twitter/X, TikTok, YouTube, WhatsApp ou Telegram.',
  'redes_sociais.url': 'URL completa do perfil na rede social. Exemplo: https://www.instagram.com/nomedousuario/',
  
  // StepLocalizacao - Endereço
  cep: 'CEP para localização geográfica do lead. Digite o CEP e clique fora do campo para preenchimento automático dos dados de endereço.',
  endereco: 'Nome da rua, avenida ou logradouro onde o lead reside. Será preenchido automaticamente ao consultar o CEP.',
  numero: 'Número do imóvel/residência. Campo numérico obrigatório para endereçamento completo.',
  complemento: 'Informações complementares do endereço: apartamento, bloco, sala, andar, ponto de referência, etc.',
  bairro: 'Bairro onde o lead reside. Importante para segmentação geográfica e análise de mercado.',
  cidade: 'Cidade de residência do lead. Preenchida automaticamente ao consultar o CEP.',
  estado: 'Estado/UF de residência. Selecionado automaticamente ao consultar o CEP.',
  
  // StepBanco - Dados Bancários
  banco_codigo: 'Código do banco (COMPE) com 3 dígitos. Exemplo: 001 para Banco do Brasil, 033 para Santander.',
  banco_nome: 'Nome completo do banco onde o lead possui conta. Será preenchido automaticamente ao selecionar o código do banco.',
  agencia: 'Número da agência bancária. Digite apenas números, sem o dígito verificador.',
  conta: 'Número da conta corrente ou poupança. Inclua o dígito verificador se houver.',
  conta_tipo: 'Tipo de conta bancária: Conta Corrente (para transações diárias) ou Conta Poupança (para investimentos).',
  pix_tipo: 'Tipo da chave PIX cadastrada pelo lead. Opções: CPF, Celular, E-mail ou Chave Aleatória.',
  pix: 'Chave PIX para transferências bancárias. O formato varia conforme o tipo selecionado: CPF, telefone, e-mail ou código aleatório.',
  
  // Placeholder específico para PIX
  placeholderPix: {
    cpf: 'Digite o CPF (000.000.000-00)',
    celular: 'Digite o número de celular com DDD (11) 99999-9999',
    email: 'Digite o e-mail (exemplo@email.com)',
    aleatorio: 'Cole a chave aleatória PIX (geralmente 32-36 caracteres)',
    default: 'Digite a chave PIX conforme o tipo selecionado acima'
  },
  
  // StepGerenciamento - Gerenciamento do Lead
  corretor_id: 'Corretor responsável pelo atendimento e acompanhamento do lead. Se não atribuído, o lead ficará disponível no rodízio.',
  adicionar_rodizio: 'Marque esta opção para incluir o lead no sistema de rodízio automático. Leads no rodízio serão distribuídos entre corretores disponíveis.',
  status: 'Situação atual do lead no sistema. Opções: Ativo (em acompanhamento), Inativo (sem contato), Convertido (virou cliente) ou Arquivado (histórico).',
  
  // Status detalhados
  status_detalhes: {
    ativo: 'Lead em acompanhamento ativo. Receberá comunicações e será priorizado nas ações de prospecção.',
    inativo: 'Lead que não demonstrou interesse recente ou não responde aos contatos. Pode ser reativado posteriormente.',
    convertido: 'Lead que se tornou cliente. Será movido para a base de clientes e não receberá mais prospecção.',
    arquivado: 'Lead com histórico preservado, mas sem acompanhamento ativo. Útil para manter registro de contatos passados.'
  },
  
  // Dicas gerais
  dicas_gerais: {
    contato_multiplo: 'Sempre adicione mais de um número de contato para aumentar as chances de comunicação.',
    rede_social: 'Redes sociais ajudam a entender o perfil de consumo e interesses do lead.',
    dados_bancarios: 'Dados bancários são opcionais, mas facilitam transações futuras com o lead.',
    rodizio: 'Leads no rodízio são distribuídos igualmente entre corretores, garantindo tratamento justo.',
    status_lead: 'Atualize regularmente o status do lead para manter o funil de vendas organizado.'
  },
  
  // Validações importantes
  validacoes: {
    email_unico: 'Cada lead deve ter um e-mail único no sistema. Não é permitido cadastro duplicado.',
    cpf_valido: 'O CPF será validado automaticamente. Digite corretamente para evitar problemas futuros.',
    telefone_valido: 'Números de telefone devem conter DDD e o número completo com 8 ou 9 dígitos.',
    cep_valido: 'O CEP deve conter 8 dígitos. Use o formato 00000-000 ou apenas números.',
    data_nascimento_valida: 'Data deve ser anterior à data atual e o lead deve ter pelo menos 18 anos para alguns tipos de negócio.'
  }
}