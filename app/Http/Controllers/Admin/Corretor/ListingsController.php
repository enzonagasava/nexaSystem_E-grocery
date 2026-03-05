<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Corretor\StoreListingRequest;
use App\Http\Requests\Admin\Corretor\UpdateListingRequest;
use App\Models\Imovel;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ListingsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $listings = Listing::with('imovel.imagens')->paginate(20);
            return response()->json($listings);
        }

        $listings = Listing::with('imovel.imagens')
            ->paginate(20)
            ->through(fn($listing) => [
                'id' => $listing->id,
                'anuncio_ativo' => $listing->anuncio_ativo,
                'anuncio_status' => $listing->anuncio_status,
                'created_at' => $listing->created_at,
                'imovel' => $listing->imovel ? [
                    'id' => $listing->imovel->id,
                    'nome' => $listing->imovel->nome,
                    'codigo' => $listing->imovel->codigo,
                    'categoria' => $listing->imovel->categoria,
                    'cidade' => $listing->imovel->cidade,
                    'bairro' => $listing->imovel->bairro,
                    'valor_venda' => $listing->imovel->valor_venda,
                    'valor_locacao' => $listing->imovel->valor_locacao,
                    'imageUrl' => $listing->imovel->imagens->first()
                        ? asset('storage/' . $listing->imovel->imagens->first()->imagem_path)
                        : null,
                ] : null,
            ]);

        return Inertia::render('admin/corretor/Listings', [
            'listings' => $listings,
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Busca imóveis do usuário para o dropdown (com info resumida)
        $query = Imovel::query();
        if ($user && $user->id) {
            $query->where('user_id', $user->id);
        }
        
        $imoveis = $query->with('imagens')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($imovel) => [
                'id' => $imovel->id,
                'nome' => $imovel->nome,
                'codigo' => $imovel->codigo,
                'categoria' => $imovel->categoria,
                'cidade' => $imovel->cidade,
                'bairro' => $imovel->bairro,
                'valor_venda' => $imovel->valor_venda,
                'valor_locacao' => $imovel->valor_locacao,
                'listings_count' => $imovel->listings()->count(),
                'imageUrl' => $imovel->imagens->first() 
                    ? asset('storage/' . $imovel->imagens->first()->imagem_path) 
                    : null,
            ]);

        return Inertia::render('admin/corretor/ListingsCreate', [
            'imoveis' => $imoveis,
        ]);
    }

    public function store(StoreListingRequest $request)
    {
        $validated = $request->validated();
        $mode = $validated['mode'] ?? 'existing';

        DB::connection('tenant_content')->beginTransaction();

        try {
            if ($mode === 'new') {
                // Criar novo imóvel e vincular ao anúncio
                $imovelData = $validated['imovel'] ?? [];
                $imovelData['user_id'] = Auth::id();
                
                $imovel = Imovel::create($imovelData);
                $imovelId = $imovel->id;
            } else {
                // Vincular imóvel existente
                $imovelId = $validated['imovel_id'];
            }

            $listing = Listing::create([
                'imovel_id' => $imovelId,
                'anuncio_ativo' => $validated['anuncio_ativo'] ?? true,
                'anuncio_status' => $validated['anuncio_status'] ?? null,
            ]);

            DB::connection('tenant_content')->commit();

            if ($request->wantsJson()) {
                return response()->json(['listing' => $listing->load('imovel')], 201);
            }

            return redirect()->route('admin.corretor.listings.show', $listing->id);
        } catch (\Throwable $e) {
            DB::connection('tenant_content')->rollBack();
            
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Falha ao criar anúncio: ' . $e->getMessage()], 500);
            }
            
            return back()->withErrors(['general' => 'Falha ao criar anúncio. Tente novamente.']);
        }
    }

    public function show(Request $request, $id)
    {
        $listing = Listing::with([
            'imovel',
            'imovel.imagens',
            'imovel.plantas',
            'imovel.videos',
            'imovel.autorizacoes'
        ])->findOrFail($id);

        // Formata dados do imóvel para o frontend
        $listingData = $this->formatListingData($listing);

        // Busca anúncios relacionados (mesma cidade ou categoria, exceto o atual)
        $relatedListings = Listing::with('imovel.imagens')
            ->where('id', '!=', $id)
            ->where('anuncio_ativo', true)
            ->where(function($query) use ($listing) {
                if ($listing->imovel) {
                    $query->whereHas('imovel', function($q) use ($listing) {
                        $q->where('cidade', $listing->imovel->cidade)
                          ->orWhere('categoria', $listing->imovel->categoria);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'anuncio_ativo' => $l->anuncio_ativo,
                'anuncio_status' => $l->anuncio_status,
                'created_at' => $l->created_at,
                'imovel' => $l->imovel ? [
                    'id' => $l->imovel->id,
                    'nome' => $l->imovel->nome,
                    'codigo' => $l->imovel->codigo,
                    'categoria' => $l->imovel->categoria,
                    'cidade' => $l->imovel->cidade,
                    'bairro' => $l->imovel->bairro,
                    'valor_venda' => $l->imovel->valor_venda,
                    'valor_locacao' => $l->imovel->valor_locacao,
                    'imageUrl' => $l->imovel->imagens->first()
                        ? asset('storage/' . $l->imovel->imagens->first()->imagem_path)
                        : null,
                ] : null,
            ]);

        if ($request->wantsJson()) {
            return response()->json(['listing' => $listingData, 'relatedListings' => $relatedListings]);
        }

        return Inertia::render('admin/corretor/ListingsShow', [
            'listing' => $listingData,
            'relatedListings' => $relatedListings,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $listing = Listing::with([
            'imovel',
            'imovel.imagens',
            'imovel.plantas',
            'imovel.videos',
            'imovel.autorizacoes'
        ])->findOrFail($id);

        // Formata dados do imóvel para o frontend
        $listingData = $this->formatListingData($listing);

        if ($request->wantsJson()) {
            return response()->json(['listing' => $listingData]);
        }

        return Inertia::render('admin/corretor/ListingsEdit', ['listing' => $listingData]);
    }

    public function update(UpdateListingRequest $request, $id)
    {
        $listing = Listing::with('imovel')->findOrFail($id);
        $validated = $request->validated();
        
        // Atualiza dados do anúncio
        $listingData = array_filter([
            'anuncio_ativo' => $validated['anuncio_ativo'] ?? null,
            'anuncio_status' => $validated['anuncio_status'] ?? null,
        ], fn($value) => $value !== null);
        
        if (!empty($listingData)) {
            $listing->update($listingData);
        }
        
        // Atualiza dados do imóvel se fornecidos
        if (isset($validated['imovel']) && is_array($validated['imovel'])) {
            $imovelData = $validated['imovel'];
            $listing->imovel->update($imovelData);
        }
        
        // Recarrega com relacionamentos para retornar dados atualizados
        $listing->load([
            'imovel',
            'imovel.imagens',
            'imovel.plantas',
            'imovel.videos',
            'imovel.autorizacoes'
        ]);

        if ($request->wantsJson()) {
            return response()->json(['listing' => $this->formatListingData($listing)]);
        }

        return redirect()->route('admin.corretor.listings.index');
    }

    public function destroy(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);
        $listing->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.corretor.listings.index');
    }

    /**
     * Formata dados do listing com imóvel para o frontend
     */
    private function formatListingData(Listing $listing): array
    {
        $imovel = $listing->imovel;
        
        $imagens = $imovel->imagens->map(fn($img) => [
            'id' => $img->id,
            'url' => $img->imagem_path ? asset('storage/' . $img->imagem_path) : null,
            'ordem' => $img->ordem,
            'original_name' => $img->original_name,
        ])->toArray();

        $plantas = $imovel->plantas->map(fn($p) => [
            'id' => $p->id,
            'url' => route('admin.corretor.imoveis.planta.download', ['id' => $imovel->id, 'planta' => $p->id]),
            'original_name' => $p->original_name,
            'mime_type' => $p->mime_type,
        ])->toArray();

        $videos = $imovel->videos->map(fn($v) => [
            'id' => $v->id,
            'url' => route('admin.corretor.imoveis.video.stream', ['id' => $imovel->id, 'video' => $v->id]),
            'original_name' => $v->original_name,
            'mime_type' => $v->mime_type,
        ])->toArray();

        return [
            'id' => $listing->id,
            'anuncio_ativo' => $listing->anuncio_ativo,
            'anuncio_status' => $listing->anuncio_status,
            'created_at' => $listing->created_at,
            'updated_at' => $listing->updated_at,
            'imovel' => [
                'id' => $imovel->id,
                'codigo' => $imovel->codigo,
                'nome' => $imovel->nome,
                'descricao' => $imovel->descricao,
                'status' => $imovel->status,
                'categoria' => $imovel->categoria,
                'finalidade' => $imovel->finalidade,
                'modalidade' => $imovel->modalidade,
                'condicao' => $imovel->condicao,
                'exclusividade' => $imovel->exclusividade,
                'endereco' => [
                    'cep' => $imovel->cep,
                    'estado' => $imovel->estado,
                    'cidade' => $imovel->cidade,
                    'bairro' => $imovel->bairro,
                    'endereco' => $imovel->endereco,
                    'numero' => $imovel->numero,
                    'complemento' => $imovel->complemento,
                    'referencia' => $imovel->referencia,
                    'mostrar_endereco_completo' => $imovel->mostrar_endereco_completo,
                ],
                'valores' => [
                    'valor_venda' => $imovel->valor_venda,
                    'valor_locacao' => $imovel->valor_locacao,
                    'valor_condominio' => $imovel->valor_condominio,
                    'valor_iptu' => $imovel->valor_iptu,
                    'aceita_financiamento' => $imovel->aceita_financiamento,
                    'aceita_permuta' => $imovel->aceita_permuta,
                    'comissao_percent' => $imovel->comissao_percent,
                    'comissao_valor' => $imovel->comissao_valor,
                ],
                'caracteristicas' => [
                    'area_total' => $imovel->area_total,
                    'area_construida' => $imovel->area_construida,
                    'area_util' => $imovel->area_util,
                    'quartos' => $imovel->quartos,
                    'suites' => $imovel->suites,
                    'banheiros' => $imovel->banheiros,
                    'vagas' => $imovel->vagas,
                    'salas' => $imovel->salas,
                    'andar' => $imovel->andar,
                    'ano_construcao' => $imovel->ano_construcao,
                    'mobilia' => $imovel->mobilia,
                    'itens' => $imovel->itens,
                    'areas_lazer' => $imovel->areas_lazer,
                    'varanda' => $imovel->varanda,
                ],
                'proprietario' => [
                    'nome' => $imovel->proprietario_nome,
                    'telefone' => $imovel->proprietario_telefone,
                    'email' => $imovel->proprietario_email,
                    'documento' => $imovel->proprietario_documento,
                ],
                'imagens' => $imagens,
                'plantas' => $plantas,
                'videos' => $videos,
                'imageUrl' => count($imagens) > 0 ? $imagens[0]['url'] : null,
            ],
        ];
    }
}
