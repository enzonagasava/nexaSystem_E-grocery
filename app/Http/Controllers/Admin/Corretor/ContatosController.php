<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Cliente;
use App\Models\User;
use App\Models\ClienteImobiliaria;
use App\Models\Imovel;
use App\Models\KanbanQuadro;

class ContatosController extends Controller
{
    public function index(Request $request): Response
    {
        $contatos = ClienteImobiliaria::with(['corretor', 'status'])
        ->where('status_cliente', 'contato')
        ->orderBy('nome_completo')
        ->get();

        // Busca o quadro do tipo 'leads' e seus status através das colunas
        $quadroLeads = KanbanQuadro::with('statuses')
            ->where('tipo', 'contatos')
            ->first();

        $status = $quadroLeads ? $quadroLeads->statuses : collect();

        return Inertia::render('admin/corretor/contatos/Contatos', [
            'contatos' => $contatos,
            'status' => $status
        ]);
    }

    public function create(): Response
    {
        $corretor = User::select('id', 'name')->get();
        $imovel = Imovel::get();
        
        $leads = ClienteImobiliaria::with(['corretor', 'status'])
            ->where('status_cliente', 'lead')
            ->orderBy('nome_completo')
            ->get();

        // Busca o quadro do tipo 'leads' e seus status através das colunas
        $quadroLeads = KanbanQuadro::with('statuses')
            ->where('tipo', 'contatos')
            ->first();

        $status = $quadroLeads ? $quadroLeads->statuses : collect();
        
        return Inertia::render('admin/corretor/contatos/ContatosCreate', [
            'corretor' => $corretor,
            'imovel' => $imovel,
            'leads' => $leads,
            'status' => $status
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
             'nome_completo' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:tenant_content.leads,email',
            'status_id' => 'required|integer',
            'status_cliente' => 'string',
                        
            // Campos opcionais
            'genero' => 'nullable|string|in:masculino,feminino,outro,nao_informar',
            'data_nascimento' => 'nullable|date|before:today',
            'redes_sociais' => 'nullable|array',
            'redes_sociais.*.plataforma' => 'nullable|string|in:Facebook,Instagram,LinkedIn,TikTok,Twitter,Outro',
            'redes_sociais.*.url' => 'nullable|url|max:255',
            'cpf' => 'nullable|string|max:14|unique:tenant_content.leads,cpf',
            'rg' => 'nullable|string|max:20',
            
            // Endereço
            'cep' => 'nullable|string|max:10|regex:/^\d{5}-?\d{3}$/',
            'endereco' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2|regex:/^[A-Z]{2}$/',
            'complemento' => 'nullable|string|max:255',
            'numero' => 'nullable',
            
            // Dados bancários
            'banco_nome' => 'nullable|string|max:100',
            'banco_codigo' => 'nullable|string|max:10|regex:/^\d+$/',
            'agencia' => 'nullable|string|max:20',
            'agencia_digito' => 'nullable|string|max:5',
            'conta' => 'nullable|string|max:30',
            'conta_digito' => 'nullable|string|max:5',
            'conta_tipo' => 'nullable|string|in:corrente,poupança,salario',
            
            // PIX
            'pix' => 'nullable|string|max:100',
            'pix_tipo' => 'nullable|string|in:cpf,cnpj,email,telefone,aleatorio',
            
            // Gerenciamento
            'corretor_id' => 'nullable|integer',
            'adicionar_rodizio' => 'nullable|boolean',
            'observacoes' => 'nullable|string|max:1000',
            'imovel_id' => 'nullable',

            'como_conheceu' => 'nullable|string|max:255',
            'corretor_id' => 'nullable|integer',
            'lead_id' => 'nullable|integer',
            'nivel_interesse' => 'nullable|string',
            'observacoes' => 'nullable|string',
            'origem' => 'nullable|string',
            'preferencias_caracteristicas' => 'nullable|string',
            'preferencias_imovel_tipos' => 'nullable|array',
            'preferencias_localizacao' => 'nullable|string',
            'preferencias_modalidade' => 'nullable|string',
            'preferencias_preco_max' => 'nullable|integer',
            'preferencias_preco_min' => 'nullable|integer',
            'profissao' => 'nullable|string',
            'renda_familiar' => 'nullable|integer',
            'renda_mensal' => 'nullable|integer',
            'tipo_relacao' => 'nullable|string'
        ], [
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'nome_completo.max' => 'O nome completo não pode ter mais que :max caracteres.',
            
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado em nossa base.',
            
            'status_id.required' => 'O status é obrigatório.',
            'status_id.in' => 'O status selecionado é inválido.',
            
            'cpf.unique' => 'Este CPF já está cadastrado em nossa base.',
            'cpf.regex' => 'Informe um CPF válido (formato: 000.000.000-00 ou 00000000000).',
            
            'data_nascimento.before' => 'A data de nascimento não pode ser futura.',
            
            'cep.regex' => 'Informe um CEP válido (formato: 00000-000 ou 00000000).',
            'estado.regex' => 'Informe uma UF válida (ex: SP, RJ, MG).',
            
            'banco_codigo.regex' => 'O código do banco deve conter apenas números.',


            // Como conheceu
            'como_conheceu.string' => 'O campo "Como conheceu" deve conter texto válido.',
            'como_conheceu.max' => 'O campo "Como conheceu" não pode ter mais que 255 caracteres.',
            
            // Corretor
            'corretor_id.integer' => 'O ID do corretor deve ser um número válido.',
            
            // Lead
            'lead_id.string' => 'O ID do lead deve ser um texto válido.',
            'lead_id.max' => 'O ID do lead não pode ter mais que 50 caracteres.',
            
            // Nível de Interesse
            'nivel_interesse.string' => 'O campo "Nível de Interesse" deve conter texto válido.',
            
            // Observações
            'observacoes.string' => 'O campo "Observações" deve conter texto válido.',
            
            // Origem
            'origem.string' => 'O campo "Origem" deve conter texto válido.',
            
            // Preferências - Características
            'preferencias_caracteristicas.string' => 'O campo "Características desejadas" deve conter texto válido.',
            
            // Preferências - Tipos de Imóvel
            'preferencias_imovel_tipos.array' => 'O campo "Tipos de imóvel" deve ser uma lista válida.',
            
            // Preferências - Localização
            'preferencias_localizacao.string' => 'O campo "Localização preferida" deve conter texto válido.',
            
            // Preferências - Modalidade
            'preferencias_modalidade.string' => 'O campo "Modalidade" deve conter texto válido.',
            
            // Preferências - Preços
            'preferencias_preco_max.integer' => 'O preço máximo deve ser um número inteiro válido.',
            'preferencias_preco_min.integer' => 'O preço mínimo deve ser um número inteiro válido.',
            
            // Profissão
            'profissao.string' => 'O campo "Profissão" deve conter texto válido.',
            
            // Rendas
            'renda_familiar.integer' => 'A renda familiar deve ser um número inteiro válido.',
            'renda_mensal.integer' => 'A renda mensal deve ser um número inteiro válido.',
            
            // Status
            'status.string' => 'O campo "Status" deve conter texto válido.',
            
            // Tipo de Relação
            'tipo_relacao.string' => 'O campo "Tipo de Relação" deve conter texto válido.',
        ]);

        $lead = ClienteImobiliaria::create($data);

        return response()->json([
                        'success' => true,
                        'message' => 'Lead criado com sucesso!',
                        'data' => $lead
                    ], 201);
                
        }

    public function edit(int $id): Response
    {
        $cliente = Cliente::findOrFail($id);
        return Inertia::render('admin/corretor/contatos/ContatosCreate', ['cliente' => $cliente]);
    }

    public function update(Request $request, int $id)
    {
        $cliente = ClienteImobiliaria::findOrFail($id);

        $cliente->update($request->all());

        return response()->json([
                'success' => true,
                'message' => 'Contato atualizado com sucesso!',
                'data' => $cliente
            ], 201);
    }

    public function destroy(int $id)
    {
        Cliente::destroy($id);
        return redirect()->route('admin.corretor.contatos.index')->with('success', 'Contato removido.');
    }

    public function show($id)
    {
        $contato =  ClienteImobiliaria::with(['imovel', 'corretor', 'status'])->findOrFail($id);
            
        return response()->json([
            'contato' => $contato
        ]);
    }
}
