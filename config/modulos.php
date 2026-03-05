<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Módulos
    |--------------------------------------------------------------------------
    | Lista de módulos do sistema. Ao adicionar um novo módulo, inclua aqui
    | e em painel_modulos (quais tipos de painel terão acesso). O sync e o
    | seeder de permissões usam esta config para criar/atualizar o banco.
    */
    'modulos' => [
        ['nome' => 'chat', 'display_name' => 'Chat', 'descricao' => 'Módulo de chat'],
        ['nome' => 'agenda', 'display_name' => 'Agenda', 'descricao' => 'Agenda e calendário'],
        ['nome' => 'imoveis', 'display_name' => 'Imóveis', 'descricao' => 'Gestão de imóveis'],
        ['nome' => 'pacientes', 'display_name' => 'Pacientes', 'descricao' => 'Cadastro de pacientes'],
        ['nome' => 'financeiro', 'display_name' => 'Financeiro', 'descricao' => 'Módulo financeiro'],
        ['nome' => 'leads', 'display_name' => 'Leads', 'descricao' => 'Gestão de leads'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ações padrão por recurso
    |--------------------------------------------------------------------------
    | Para cada módulo são criadas permissões com estas ações. O nome completo
    | da permissão fica no formato recurso.acao (ex.: chat.visualizar).
    */
    'acoes' => [
        ['nome' => 'visualizar', 'display_suffix' => 'Visualizar'],
        ['nome' => 'criar', 'display_suffix' => 'Criar'],
        ['nome' => 'editar', 'display_suffix' => 'Editar'],
        ['nome' => 'excluir', 'display_suffix' => 'Excluir'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Painel × Módulos
    |--------------------------------------------------------------------------
    | Associação do nome do tipo de painel (tipo_painel.nome em nexa_admin)
    | com os nomes dos módulos disponíveis para aquele painel.
    */
    'painel_modulos' => [
        'CRM Ecommerce' => ['chat', 'agenda', 'financeiro'],
        'CRM Clínica' => ['chat', 'agenda', 'pacientes', 'financeiro'],
        'CRM Imobiliário' => ['chat', 'agenda', 'imoveis', 'leads', 'financeiro'],
    ],

];
