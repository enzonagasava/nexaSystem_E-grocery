<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\ClienteImobiliaria;
use App\Models\Imovel;
use App\Models\Contato;
use App\Models\Status;
use App\Models\KanbanColuna;
use App\Models\KanbanQuadro;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log; 


class LeadsController extends Controller
{
    public function index(): Response
    {
        $leads = ClienteImobiliaria::with(['corretor', 'status'])
            ->where('status_cliente', 'lead')
            ->orderBy('nome_completo')
            ->get();

        return Inertia::render('admin/corretor/leads/Leads', [
            'leads' => $leads,
        ]);
    }

    public function create(): Response
    {
        $corretor = User::select('id', 'name')->get();
        $imovel = Imovel::get();
        
        // Busca o quadro do tipo 'leads' e seus status através das colunas
        $quadroLeads = KanbanQuadro::with('statuses')
            ->where('tipo', 'leads')
            ->first();

        $status = Status::whereHas('quadro', function ($query) {
            $query->where('tipo', 'leads');
        })->with('quadro')->get();

        return Inertia::render('admin/corretor/leads/LeadsCreate', [
            'corretor' => $corretor,
            'imovel' => $imovel,
            'status' => $status
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome_completo' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:tenant_content.cliente_imobiliaria,email',
            'status_id' => 'required|integer',
            
            // Campos de contatos
            'contatos' => 'required|array',
            'contatos.*.numero' => 'required',
            'contatos.*.tipo' => 'required|string',
            'contatos.*.principal' => 'nullable|boolean',
            
            // Campos opcionais
            'genero' => 'nullable|string|in:masculino,feminino,outro,nao_informar',
            'data_nascimento' => 'nullable|date|before:today',
            'redes_sociais' => 'nullable|array',
            'redes_sociais.*.plataforma' => 'nullable|string|in:Facebook,Instagram,LinkedIn,TikTok,Twitter,Outro',
            'redes_sociais.*.url' => 'nullable|url|max:255',
            'cpf' => 'nullable|string|max:14|unique:tenant_content.cliente_imobiliaria,cpf',
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
        ], [
            // Mensagens de erro
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'nome_completo.max' => 'O nome completo não pode ter mais que :max caracteres.',
            
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado em nossa base.',
            
            'status_id.required' => 'O status é obrigatório.',
            'status_id.in' => 'O status selecionado é inválido.',
            
            'contatos.required' => 'Adicione pelo menos um contato.',
            'contatos.min' => 'Adicione pelo menos :min contato.',
            'contatos.*.numero.required' => 'O número do contato é obrigatório.',
            'contatos.*.tipo.required' => 'O tipo do contato é obrigatório.',
            'contatos.*.tipo.in' => 'O tipo de contato selecionado é inválido.',
            
            'cpf.unique' => 'Este CPF já está cadastrado em nossa base.',
            'cpf.regex' => 'Informe um CPF válido (formato: 000.000.000-00 ou 00000000000).',
            
            'data_nascimento.before' => 'A data de nascimento não pode ser futura.',
            
            'cep.regex' => 'Informe um CEP válido (formato: 00000-000 ou 00000000).',
            'estado.regex' => 'Informe uma UF válida (ex: SP, RJ, MG).',
            
            'banco_codigo.regex' => 'O código do banco deve conter apenas números.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        // ✅ PEGA OS DADOS VALIDADOS
        $data = $validator->validated();
         
        // Processar os contatos para garantir que um seja marcado como principal
        if (isset($data['contatos'])) {
            $hasPrincipal = collect($data['contatos'])->contains('principal', true);
            if (!$hasPrincipal && count($data['contatos']) > 0) {
                $data['contatos'][0]['principal'] = true;
            }
        }

        // Processar redes sociais se existirem
        if (isset($data['redes_sociais']) && empty($data['redes_sociais'][0])) {
            $data['redes_sociais'] = [];
        }

        // Adicionar corretor_id se não foi enviado
        if (!isset($data['corretor_id'])) {
            $data['corretor_id'] = auth()->user()->corretor_id ?? null;
        }
        
        try {
            $lead = ClienteImobiliaria::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Lead criado com sucesso!',
                'data' => $lead
            ], 201);
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Erro ao criar lead: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $lead = ClienteImobiliaria::with(['imovel', 'corretor', 'status'])->findOrFail($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead não encontrado'], 404);
        }   

        $validator = Validator::make($request->all(), [
            'nome_completo' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'status_id' => 'required|integer',
            
            // Campos de contatos
            'contatos' => 'required|array',
            'contatos.*.numero' => 'required',
            'contatos.*.tipo' => 'required|string',
            'contatos.*.principal' => 'nullable|boolean',
            
            // Campos opcionais
            'genero' => 'nullable|string|in:masculino,feminino,outro,nao_informar',
            'data_nascimento' => 'nullable|date|before:today',
            'redes_sociais' => 'nullable|array',
            'redes_sociais.*.plataforma' => 'nullable|string|in:Facebook,Instagram,LinkedIn,TikTok,Twitter,Outro',
            'redes_sociais.*.url' => 'nullable|url|max:255',
            'cpf' => 'nullable|string|max:14',
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
        ], [
            // Mensagens de erro
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'nome_completo.max' => 'O nome completo não pode ter mais que :max caracteres.',
            
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado em nossa base.',
            
            'status_id.required' => 'O status é obrigatório.',
            'status_id.in' => 'O status selecionado é inválido.',
            
            'contatos.required' => 'Adicione pelo menos um contato.',
            'contatos.min' => 'Adicione pelo menos :min contato.',
            'contatos.*.numero.required' => 'O número do contato é obrigatório.',
            'contatos.*.tipo.required' => 'O tipo do contato é obrigatório.',
            'contatos.*.tipo.in' => 'O tipo de contato selecionado é inválido.',
            
            'cpf.unique' => 'Este CPF já está cadastrado em nossa base.',
            'cpf.regex' => 'Informe um CPF válido (formato: 000.000.000-00 ou 00000000000).',
            
            'data_nascimento.before' => 'A data de nascimento não pode ser futura.',
            
            'cep.regex' => 'Informe um CEP válido (formato: 00000-000 ou 00000000).',
            'estado.regex' => 'Informe uma UF válida (ex: SP, RJ, MG).',
            
            'banco_codigo.regex' => 'O código do banco deve conter apenas números.',
        ]);

        $data = $validator->validated();

        // Adiciona unique apenas se o email for diferente do original
        if ($request->email !== $lead->email) {
            $rules['email'] = 'required|email|max:255|unique:tenant_content.leads,email';
        }

        // Adiciona unique apenas se o CPF for diferente do original
        if ($request->cpf && $request->cpf !== $lead->cpf) {
            $rules['cpf'] = 'nullable|string|max:14|unique:tenant_content.leads,cpf';
        }

        // Processar os contatos
        if (isset($data['contatos'])) {
            $hasPrincipal = collect($data['contatos'])->contains('principal', true);
            if (!$hasPrincipal && count($data['contatos']) > 0) {
                $data['contatos'][0]['principal'] = true;
            }
        }

        if (isset($data['redes_sociais']) && empty($data['redes_sociais'][0])) {
            $data['redes_sociais'] = [];
        }

        try {

            $lead->update($data);

            //Carrega os novos dados
            $lead->load('status');

            
            return response()->json([
                'success' => true,
                'message' => 'Lead atualizado com sucesso!',
                'data' => $lead
            ], 201);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Erro ao atualizar lead: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(int $id): Response
    {
        $lead = ClienteImobiliaria::with('imovel')->findOrFail($id);
        $imovel = Imovel::get();
        $quadroLeads = KanbanQuadro::with('statuses')
                    ->where('tipo', 'leads')
                    ->first();

        $status = $quadroLeads ? $quadroLeads->statuses : collect();


        return Inertia::render('admin/corretor/leads/LeadsCreate', [
            'lead' => $lead,
            'imovel' => $imovel,
            'status' => $status
            ]);
    }

    public function destroy(int $id)
    {
        ClienteImobiliaria::destroy($id);
        return redirect()->route('admin.corretor.leads.index')->with('success', 'Lead removido.');
    }


    public function show(int $id): JsonResponse
    {
        // Rota para API (JSON) usada pela modal
        $lead = ClienteImobiliaria::with(['imovel', 'corretor', 'status'])->findOrFail($id);
        $imovel = Imovel::get();

        $status = Status::whereHas('quadro', function ($query) {
            $query->where('tipo', 'leads');
        })->with('quadro')->get();

        return response()->json([
            'lead' => $lead,
            'imovel' => $imovel,
            'status' => $status
        ]);

    }   

    public function converterLeadParaContato(Request $request, $id)
    {
        try {
            
            $lead = ClienteImobiliaria::findOrFail($id);
            
            // Verifica se já foi convertido
            $contatoExistente = ClienteImobiliaria::where('id', $lead->id)->first();

            if ($contatoExistente->status_cliente == 'contato') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este lead já foi convertido para contato'
                ], 400);
            }
            
            // Busca o quadro do tipo 'leads' e seus status através das colunas
            $quadroLeads = KanbanQuadro::with('statuses')
                ->where('tipo', 'contatos')
                ->first();

            $status = $quadroLeads->statuses()->first()->id;


            $lead->status_cliente = 'contato';
            $lead->status_id = $status;
            $lead->save();
            
            
            return response()->json([
                'success' => true,
                'message' => 'Lead convertido para contato com sucesso',
                'lead' => $lead
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao converter lead: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao converter lead: ' . $e->getMessage()
            ], 500);
        }
    }


}
