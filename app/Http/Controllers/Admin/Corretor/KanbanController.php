<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\ClienteImobiliaria;
use App\Models\Contato;
use App\Models\KanbanQuadro;
use App\Models\KanbanColuna;
use App\Models\KanbanCard;
use App\Models\Status;
use Google\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 

class KanbanController extends Controller
{
    /**
     * Display the kanban board.
     */
    public function index(Request $request): Response
    {
        $usuarioId = auth()->id();
        
        // Busca ou cria o quadro padrão para o usuário
        $quadro = $this->getOrCreateQuadroPadrao($usuarioId, 'leads');

        // Busca as colunas do quadro com seus status ativos
        $colunas = KanbanColuna::where('kanban_quadro_id', $quadro->id,)
            ->whereHas('status', function($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['status' => function($query) {
                $query->whereNull('deleted_at');
            }])
            ->get()
            ->sortBy('status.ordem')
            ->values();

         if ($quadro->tipo === 'leads') {
                // Busca todos os leads
                $todosLeads = ClienteImobiliaria::with(['corretor'])
                    ->get();
                
                $leads = $todosLeads;

                // Organiza por coluna baseado no status
                foreach ($colunas as $coluna) {
                    $leadsFiltrados = $todosLeads->filter(function ($lead) use ($coluna) {
                        return $this->leadCorrespondeColuna($lead, $coluna);
                    });
                    
                    $cardsPorColuna[$coluna->id] = [
                        'coluna' => $coluna,
                        'leads' => $leadsFiltrados->values()
                    ];
                }
            } else {
                // Para contatos (se você tiver essa funcionalidade)
                $todosContatos = Contato::with(['corretor'])
                    ->get();
                
                $leads = $todosContatos;

                // Organiza por coluna
                foreach ($colunas as $coluna) {
                    $contatosFiltrados = $todosContatos->filter(function ($contato) use ($coluna) {
                        return $contato->status === $coluna->titulo;
                    });
                    
                    $cardsPorColuna[$coluna->id] = [
                        'coluna' => $coluna,
                        'leads' => $contatosFiltrados->values()
                    ];
                }
            }


            // Formata os leads para o frontend
            $leadsFormatados = collect($leads)->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'nome_completo' => $lead->nome_completo,
                    'email' => $lead->email,
                    'status' => $lead->status,
                    'cidade' => $lead->cidade,
                    'estado' => $lead->estado,
                    'valor_desejado' => $lead->valor_desejado,
                    'adicionar_rodizio' => $lead->adicionar_rodizio,
                    'contatos' => $lead->contatos,
                    'corretor_id' => $lead->corretor_id,
                    'corretor' => $lead->corretor ? [
                        'id' => $lead->corretor->id,
                        'name' => $lead->corretor->name
                    ] : null,
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->updated_at,
                ];
            });


        $todosQuadros = KanbanQuadro::get();
        

        
        // Busca os leads com seus dados
        $leads = ClienteImobiliaria::with(['corretor'])
            ->orderBy('nome_completo')
            ->get();
        
        // Organiza os cards por coluna baseado no status
        $cards = $this->organizarCardsPorColuna($leads, $colunas);
        
        return Inertia::render('admin/corretor/kanban/Kanban', [
            'quadro' => [
                    'id' => $quadro->id,    
                    'nome' => $quadro->nome,
                    'tipo' => $quadro->tipo,
                    'descricao' => $quadro->descricao,
                    'is_active' => $quadro->is_active,
                    'total_leads' => $leadsFormatados->count()
                ],
            'colunas' => $colunas,
            'cards' => $cards,
            'leads' => $leads,
            'todosQuadros' => $todosQuadros
        ]);
    }

    /**
     * Get or create default board for user.
     */
    private function getOrCreateQuadroPadrao(int $usuarioId, string $tipo = 'leads'): KanbanQuadro
    {
        $quadro = KanbanQuadro::where('user_id', $usuarioId)
            ->where('tipo', $tipo)
            ->where('is_active', true)
            ->first();
        
        if (!$quadro) {
            // Tenta buscar um quadro compartilhado
            $quadro = KanbanQuadro::whereJsonContains('permissao_users', $usuarioId)
                ->where('tipo', $tipo)
                ->where('is_active', true)
                ->first();
        }
        
        if (!$quadro) {
            // Cria um novo quadro baseado no template padrão
            $quadro = $this->criarQuadroPadrao($usuarioId, $tipo);
        }
        
        return $quadro;
    }

    public function getQuadroInfo(Request $request, $id)
    {
        try {
            // Busca o quadro pelo ID
            $quadro = KanbanQuadro::where('id', $id)
                ->where('is_active', true)
                ->firstOrFail();

            // Busca as colunas do quadro com seus status ativos
            $colunas = KanbanColuna::where('kanban_quadro_id', $quadro->id)
                ->whereHas('status', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->with(['status' => function($query) {
                    $query->whereNull('deleted_at');
                }])
                ->get()
                ->sortBy('status.ordem')
                ->values();

            // Busca todos os clientes (leads ou contatos) baseado no tipo do quadro
            $clientes = ClienteImobiliaria::with(['corretor', 'status'])
                ->when($quadro->tipo, function ($query, $tipo) {
                    // Se o quadro é de leads, filtra por status_cliente = 'lead'
                    // Se é de contatos, filtra por status_cliente = 'contato'
                    return $query->where('status_cliente', $tipo === 'leads' ? 'lead' : 'contato');
                })
                ->get();

            // Formata os itens
            $itemsFormatados = $clientes->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nome_completo' => $cliente->nome_completo,
                    'email' => $cliente->email,
                    'status' => $cliente->status ? $cliente->status->id : null,
                    'cidade' => $cliente->cidade,
                    'estado' => $cliente->estado,
                    'valor_desejado' => $cliente->valor_desejado,
                    'adicionar_rodizio' => $cliente->adicionar_rodizio,
                    'contatos' => $cliente->contatos,
                    'corretor_id' => $cliente->corretor_id,
                    'corretor' => $cliente->corretor ? [
                        'id' => $cliente->corretor->id,
                        'name' => $cliente->corretor->name
                    ] : null,
                    'tipo' => $cliente->status_cliente, // 'lead' ou 'contato'
                    'created_at' => $cliente->created_at,
                    'updated_at' => $cliente->updated_at,
                ];
            });

            // Organiza por coluna baseado no status
            $cardsPorColuna = [];
            foreach ($colunas as $coluna) {
                // Verifica se a coluna tem status
                $clientesFiltrados = $clientes->filter(function ($cliente) use ($coluna) {
                    // Só compara se AMBOS tiverem status
                    if ($cliente->status && $coluna->status) {
                        return $cliente->status->id == $coluna->status->id;
                    }
                    return false;
                });

                $cardsPorColuna[$coluna->id] = [
                    'coluna' => $coluna,
                    'leads' => $clientesFiltrados->values()->map(function ($cliente) {
                        return [
                            'id' => $cliente->id,
                            'nome_completo' => $cliente->nome_completo,
                            'email' => $cliente->email,
                            'status' => $cliente->status ? $cliente->status->id : null,
                            'cidade' => $cliente->cidade,
                            'estado' => $cliente->estado,
                            'valor_desejado' => $cliente->valor_desejado,
                            'adicionar_rodizio' => $cliente->adicionar_rodizio,
                            'contatos' => $cliente->contatos,
                            'corretor' => $cliente->corretor ? [
                                'name' => $cliente->corretor->name
                            ] : null,
                            'tipo' => $cliente->status_cliente,
                        ];
                    })
                ];
            }
            return response()->json([
                'success' => true,
                'message' => 'Dados do quadro carregados com sucesso',
                'quadro' => [
                    'id' => $quadro->id,
                    'nome' => $quadro->nome,
                    'tipo' => $quadro->tipo,
                    'descricao' => $quadro->descricao,
                    'is_active' => $quadro->is_active,
                    'total_items' => $itemsFormatados->count()
                ],
                'colunas' => $colunas->map(fn($coluna) => [
                    'id' => $coluna->id,
                    'titulo' => $coluna->titulo,
                    'descricao' => $coluna->descricao,
                    'cor' => $coluna->cor,
                    'cor_fundo' => $coluna->cor_fundo,
                    'icone' => $coluna->icone,
                    'ordem' => $coluna->ordem,
                    'status' => $coluna->status,
                ]),
                'cardsPorColuna' => $cardsPorColuna,
                'items' => $itemsFormatados
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do quadro: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do quadro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to check if lead matches column
     */
    private function leadCorrespondeColuna($lead, $coluna): bool
    {
        $statusLead = strtolower(trim($lead->status ?? ''));
        $tituloColuna = strtolower(trim($coluna->titulo ?? ''));
        
        // Mapeamento de status
        $mapa = [
            'novo' => ['novo', 'lead', 'novo lead'],
            'simulação' => ['simulação', 'simulacao', 'simulação financiamento'],
            'visita' => ['visita', 'visita agendada'],
            'negociação' => ['negociação', 'negociacao', 'contato', 'em negociação'],
            'documentação' => ['documentação', 'documentacao', 'docs'],
            'aprovação financiamento' => ['aprovação', 'aprovacao', 'financiamento'],
            'fechamento' => ['fechamento', 'fechado', 'finalizado'],
        ];
        
        foreach ($mapa as $chave => $valores) {
            if (in_array($tituloColuna, $valores) || str_contains($tituloColuna, $chave)) {
                return in_array($statusLead, $valores) || str_contains($statusLead, $chave);
            }
        }
        
        // Fallback: comparação direta
        return $statusLead === $tituloColuna;
    }
    /**
     * Create default board for user.
     */
    private function criarQuadroPadrao(int $usuarioId, string $tipo): KanbanQuadro
    {
        DB::beginTransaction();
        
        try {
            // Cria o quadro
            $quadro = KanbanQuadro::create([
                'user_id' => $usuarioId,
                'permissao_users' => [$usuarioId],
                'nome' => $tipo === 'leads' ? 'Funil de Leads' : 'Processo de Contatos',
                'descricao' => $tipo === 'leads' 
                    ? 'Acompanhamento de leads no funil de vendas'
                    : 'Acompanhamento de contatos em processo',
                'tipo' => $tipo,
                'is_active' => true,
                'ordem' => 1,
            ]);
            
            // Define as colunas padrão baseado no tipo
            $colunasPadrao = $this->getColunasPadrao($tipo);
            
            foreach ($colunasPadrao as $ordem => $coluna) {
                KanbanColuna::create([
                    'kanban_quadro_id' => $quadro->id,
                    'titulo' => $coluna['titulo'],
                    'descricao' => $coluna['descricao'],
                    'cor' => $coluna['cor'],
                    'cor_fundo' => $coluna['cor_fundo'],
                    'icone' => $coluna['icone'],
                    'ordem' => $ordem + 1,
                ]);
            }
            
            DB::commit();
            
            return $quadro;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar quadro padrão: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get default columns based on type.
     */
    private function getColunasPadrao(string $tipo): array
    {
        if ($tipo === 'contatos') {
            return [
                [
                    'titulo' => 'Documentação',
                    'descricao' => 'Contatos que estão reunindo a documentação necessária',
                    'cor' => 'orange',
                    'cor_fundo' => '#FFEDD5',
                    'icone' => 'FileText',
                ],
                [
                    'titulo' => 'Aprovação Financiamento',
                    'descricao' => 'Contatos aguardando aprovação de financiamento',
                    'cor' => 'blue',
                    'cor_fundo' => '#EFF6FF',
                    'icone' => 'Banknote',
                ],
                [
                    'titulo' => 'Fechamento',
                    'descricao' => 'Contatos na fase final de fechamento do negócio',
                    'cor' => 'green',
                    'cor_fundo' => '#D1FAE5',
                    'icone' => 'Handshake',
                ],
                [
                    'titulo' => 'Concluído',
                    'descricao' => 'Processos finalizados com sucesso',
                    'cor' => 'gray',
                    'cor_fundo' => '#F3F4F6',
                    'icone' => 'Award',
                ],
            ];
        }
        
        // Padrão para leads
        return [
            [
                'titulo' => 'Novo',
                'descricao' => 'Leads que acabaram de entrar no sistema',
                'cor' => 'blue',
                'cor_fundo' => '#EFF6FF',
                'icone' => 'UserPlus',
            ],
            [
                'titulo' => 'Simulação',
                'descricao' => 'Leads que estão em processo de simulação de financiamento',
                'cor' => 'yellow',
                'cor_fundo' => '#FEF3C7',
                'icone' => 'Calculator',
            ],
            [
                'titulo' => 'Visita',
                'descricao' => 'Leads que agendaram visita ao imóvel',
                'cor' => 'purple',
                'cor_fundo' => '#F3E8FF',
                'icone' => 'Home',
            ],
            [
                'titulo' => 'Negociação',
                'descricao' => 'Leads em negociação ativa',
                'cor' => 'green',
                'cor_fundo' => '#D1FAE5',
                'icone' => 'MessageCircle',
            ],
        ];
    }

    /**
     * Organize leads into columns based on status.
     */
    private function organizarCardsPorColuna($leads, $colunas): array
    {
        $cards = [];
        
        foreach ($colunas as $coluna) {
            // Filtra leads que correspondem ao título da coluna
            $leadsFiltrados = $leads->filter(function ($lead) use ($coluna) {
                return $this->leadCorrespondeColuna($lead, $coluna);
            });
            
            $cards[$coluna->id] = [
                'coluna' => $coluna,
                'leads' => $leadsFiltrados->values(),
            ];
        }
        
        return $cards;
    }

    /**
     * Update lead status and move between columns.
     */
    public function update(Request $request, $id)
    {
        try {    
            $lead = ClienteImobiliaria::findOrFail($id);

            $request->validate([    
                'status_id' => 'required',
                'coluna_id' => 'nullable|exists:tenant_content.kanban_colunas,id'
            ]);

            $colunas = KanbanColuna::where('id', $request->coluna_id)
                ->with('status')
                ->get()->first();


            $novoStatus = $colunas->status_id;
            
            // Atualiza o status
            $lead->status_id = $novoStatus;
            $lead->save();
           
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'data' => $lead
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status do lead: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update de status (útil para mover múltiplos leads)
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $request->validate([
                'lead_ids' => 'required|array',
                'lead_ids.*' => 'exists:leads,id',
                'status' => 'required|string',
                'coluna_id' => 'nullable|exists:kanban_colunas,id'
            ]);
            
            DB::beginTransaction();
            
            $count = 0;
            $promovidos = [];
            
            foreach ($request->lead_ids as $leadId) {
                $lead = ClienteImobiliaria::find($leadId);
                $oldStatus = $lead->status;
                $newStatus = $request->status;
                
                if ($oldStatus !== $newStatus) {
                    $lead->status = $newStatus;
                    $lead->save();
                    $count++;
                    
                    // Registra movimento
                    if ($request->coluna_id) {
                        $this->registrarMovimentoCard($lead, $request->coluna_id);
                    }
                    
                    // Promove se for negociação
                    if ($newStatus === 'Negociação' && $oldStatus !== 'Negociação') {
                        $promovidos[] = $this->promoverParaContato($lead);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$count} leads atualizados",
                'promovidos' => count($promovidos)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar leads: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get columns for a board.
     */
    public function getColunas(Request $request, int $quadroId)
    {
        $colunas = KanbanColuna::where('kanban_quadro_id', $quadroId)
            ->orderBy('ordem')
            ->get();
        
        return response()->json($colunas);
    }

    /**
     * Create a new column.
     */
    public function storeColuna(Request $request)
    {
        $request->validate([
            'kanban_quadro_id' => 'required|exists:tenant_content.kanban_quadros,id',
            'status' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string',
        ]);

        $ultimoStatus = Status::
        orderBy('ordem', 'desc')
        ->first();

        
        $novaOrdem = $ultimoStatus ? $ultimoStatus->ordem + 10 : 10;

         $status = Status::create([
            'nome' => $request->status, // título/status
            'descricao' => $request->descricao, // mesma descrição se não fornecida
            'ordem' => $novaOrdem,
        ]);

        $status->update();

        $coluna = KanbanColuna::create([
            'kanban_quadro_id' => $request->kanban_quadro_id,
            'status_id' => $status->id,
            'descricao' => $request->descricao,
            'cor' => $request->cor,
        ]);
        
        // Busca as colunas do quadro com seus status ativos
        $colunas = KanbanColuna::where('kanban_quadro_id', $request->kanban_quadro_id,)
            ->whereHas('status', function($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['status' => function($query) {
                $query->whereNull('deleted_at');
            }])
            ->get()
            ->sortBy('status.ordem')
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Coluna/Status criado com sucesso!',
            'coluna' => $colunas
        ], 201);
            
    }

    /**
     * Update a column.
     */
    public function updateColuna(Request $request, int $id)
    {
        // Validação primeiro
        $request->validate([
            'kanban_quadro_id' => 'required|exists:tenant_content.kanban_quadros,id',
            'status' => 'required|string|max:255',
            'status_id' => 'required|integer',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string',
        ]);

        // Busca a coluna Kanban existente
        $coluna = KanbanColuna::where('status_id', $request->status_id)->first();
        
        if (!$coluna) {
            return response()->json(['error' => 'Coluna não encontrada'], 404);
        }

        // Busca o Status existente
        $status = Status::find($request->status_id);
        
        if (!$status) {
            return response()->json(['error' => 'Status não encontrado'], 404);
        }

        // Atualiza o Status
        $status->update([
            'nome' => $request->status,
            'descricao' => $request->descricao,
        ]);

        // Atualiza a KanbanColuna
        $coluna->update([
            'kanban_quadro_id' => $request->kanban_quadro_id,
            'status_id' => $request->status_id, // Use o ID direto, não o objeto
            'descricao' => $request->descricao,
            'cor' => $request->cor,
        ]);

        // Recarrega com relacionamento
        $coluna->load('status');

        return response()->json([
            'message' => 'Coluna atualizada com sucesso',
            'coluna' => $coluna
        ]);
    }

    /**
     * Delete a column.
     */
    public function destroyColuna(int $id)
    {
        try {            
            // Busca a coluna
            $coluna = KanbanColuna::findOrFail($id);

            $primeiraColuna = KanbanColuna::where('kanban_quadro_id', $coluna->kanban_quadro_id,)
            ->whereHas('status', function($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['status' => function($query) {
                $query->whereNull('deleted_at');
            }])
            ->get()
            ->sortBy('status.ordem')
            ->values();

            if ($primeiraColuna) {
                ClienteImobiliaria::where('status_id', $coluna->status_id)
                    ->update([
                        'status_id' => $primeiraColuna[0]->status_id
                    ]);
            }

            $statusId = $coluna->status_id;
            
            // Verifica se o status existe
            $status = Status::find($statusId);
            
            if ($status) {
                // Soft delete no status
                $status->delete();
            }
            
            
            return response()->json([
                'message' => 'Status removido com sucesso',
                'coluna' => $coluna,
                'status_deletado' => $status ? true : false
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reorder columns.
     */
    public function reordenarColunas(Request $request)
    {
        $request->validate([
            'colunas' => 'required|array',
            'colunas.*.id' => 'required|exists:kanban_colunas,id',
            'colunas.*.ordem' => 'required|integer',
        ]);
        
        foreach ($request->colunas as $item) {
            KanbanColuna::where('id', $item['id'])->update(['ordem' => $item['ordem']]);
        }
        
        return response()->json(['message' => 'Colunas reordenadas com sucesso']);
    }

    public function storeOrdemColunas(Request $request){

        $request->validate([
            'kanban_quadro_id' => 'required|integer',
            'colunas' => 'required|array',
            'colunas.*.id' => 'nullable|integer',
            'colunas.*.ordem' => 'nullable|integer',
        ]);
        
         foreach ($request->colunas as $coluna) {
            if (isset($coluna['status']['id'])) {
                // Busca o status
                $status = Status::find($coluna['status']['id']);

                // Atualiza ordem E nome
                if($coluna['ordem']){
                    $status->ordem = $coluna['ordem'];
                }
                $status->nome = $coluna['status']['nome'];
                $status->save();

            }else {

                $status = Status::create([
                    'nome' => $coluna['status']['nome'],
                    'ordem' => $coluna['ordem'],
                ]);

                $status->update();

                $coluna = KanbanColuna::create([
                    'kanban_quadro_id' => $request->kanban_quadro_id,
                    'status_id' => $status->id,
                    'cor' => $coluna['cor']
                ]);

            }
        }

        return response()->json([
            'message' => 'Coluna atualizada com sucesso',
            'colunas' => $request->colunas
        ]);

    }
}