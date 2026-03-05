/**
 * Tooltips instrutivos para o formulário de cadastro de imóveis
 * 
 * Cada chave representa um campo do formulário e contém texto explicativo
 * para auxiliar o usuário no preenchimento correto das informações.
 */
export const imovelTooltips = {
  // StepZero - Imóvel
  modalidade: 'Defina se o imóvel será disponibilizado para venda ou locação',

  // StepIdentificacao - Identificação
  codigo: 'Código único interno para identificação e organização do imóvel no sistema',
  status: 'Situação atual do imóvel no portfólio (ativo para exibição, inativo, vendido ou reservado)',
  categoria: 'Categoria do imóvel que define os campos específicos do formulário',
  exclusividade: 'Indica se o imóvel é de venda/locação exclusiva da sua imobiliária',
  condicao: 'Estado geral do imóvel: novo (nunca habitado), usado ou lançamento',

  // StepLocalizacao - Localização
  cep: 'Digite o CEP para preenchimento automático dos campos de endereço',
  mostrar_endereco_completo: 'Se "Não", apenas bairro e cidade serão exibidos publicamente para preservar a privacidade',
  torre: 'Identificação da torre ou bloco em condomínios com múltiplas edificações',
  andar: 'Andar/pavimento onde o imóvel está localizado',
  referencia: 'Ponto de referência próximo que facilita a localização do imóvel',

  // StepCaracteristicas - Características físicas
  dormitorios: 'Número total de quartos/dormitórios (inclui suítes)',
  suites: 'Quartos com banheiro privativo',
  banheiros: 'Total de banheiros (incluindo os das suítes)',
  vagas: 'Número de vagas de estacionamento/garagem',
  salas: 'Quantidade de salas/ambientes em imóveis comerciais',
  area_construida: 'Área edificada do imóvel, incluindo paredes e estruturas',
  area_util: 'Área interna utilizável do imóvel, excluindo paredes e estruturas',
  area_total: 'Área total do imóvel incluindo varandas, garagens e áreas comuns privativas',
  area_terreno_largura: 'Medida da largura (frente) do terreno em metros',
  area_terreno_comprimento: 'Medida do comprimento (profundidade) do terreno em metros',
  ano_construcao: 'Ano em que o imóvel foi construído ou entregue',

  // StepValor - Valores
  valor_venda: 'Preço de venda ou valor mensal de locação do imóvel',
  valor_condominio: 'Valor mensal da taxa de condomínio',
  valor_iptu: 'Valor anual do Imposto Predial e Territorial Urbano',
  gas: 'Informações sobre fornecimento e valor médio de gás (se aplicável)',
  luz: 'Informações sobre consumo e valor médio de energia elétrica (se aplicável)',
  aceita_financiamento: 'Indica se o proprietário aceita venda com financiamento bancário',
  aceita_permuta: 'Indica se o proprietário aceita permuta/troca por outro imóvel',
  comissao_percent: 'Percentual de comissão sobre o valor de venda para o corretor',

  // StepProprietario - Proprietário
  proprietario_nome: 'Nome completo do proprietário do imóvel',
  proprietario_telefone: 'Telefone de contato do proprietário',
  proprietario_email: 'E-mail de contato do proprietário',
  proprietario_documento: 'CPF (pessoa física) ou CNPJ (pessoa jurídica) do proprietário',
  autorizacao: 'Documento assinado pelo proprietário autorizando a venda/locação do imóvel',

  // StepDetalhes - Detalhes e diferenciais
  descricao: 'Descrição detalhada do imóvel, destacando características e diferenciais',
  mobilia: 'Indica se o imóvel possui móveis (mobiliado, semi-mobiliado ou sem móveis)',
  itens: 'Liste os itens inclusos como eletrodomésticos, móveis planejados, etc.',
  varanda: 'Indica se o imóvel possui varanda ou sacada',
  areas_lazer: 'Áreas de lazer disponíveis no condomínio (ex: piscina, churrasqueira, salão de festas)',

  // StepMidia - Mídia
  imagens: 'Fotos do imóvel para divulgação (quanto mais, melhor a visualização)',
  planta: 'Planta/projeto do imóvel em formato PDF ou imagem',
  video: 'Vídeo de apresentação ou tour virtual do imóvel',
}
