<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Imovel;
use App\Models\ImovelImagem;
use App\Models\ImovelPlanta;
use App\Models\ImovelVideo;
use App\Models\ImovelAutorizacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\Admin\Corretor\StoreImovelRequest;
use App\Http\Requests\Admin\Corretor\UpdateImovelRequest;
use App\Http\Requests\Admin\Corretor\IndexImovelRequest;
use Illuminate\Http\JsonResponse;

class ImoveisController extends Controller
{
    /**
     * Retorna lista de cidades distintas onde existem imóveis cadastrados
     * Filtro insensível a acentos e maiúsculas
     */
    public function getCidades(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        
        $query = Imovel::select('cidade')
            ->whereNotNull('cidade')
            ->where('cidade', '!=', '')
            ->distinct();

        // Aplica busca insensível a acentos se fornecido termo
        if ($search) {
            // Remove acentos do termo de busca para comparação
            $searchNormalized = preg_replace('/[\p{M}]/u', '', normalize('NFD', $search));
            
            // Filtra cidades onde o nome normalizado contém o termo
            $query->whereRaw(
                "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cidade, 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'), 'ã', 'a'), 'õ', 'o'), 'ç', 'c'), 'â', 'a')) LIKE LOWER(CONCAT('%', ?, '%'))",
                [$searchNormalized]
            );
        }

        // Opcional: filtrar por user_id se necessário
        $user = Auth::user();
        if ($user && $user->empresa_id) {
            $query->where('user_id', $user->id);
        }

        $cidades = $query
            ->orderBy('cidade')
            ->limit($search ? 15 : 100) // Retorna mais cidades quando não há busca
            ->pluck('cidade')
            ->values();

        return response()->json($cidades);
    }

    /**
     * Retorna lista de categorias distintas onde existem imóveis cadastrados
     */
    public function getCategorias(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        
        $query = Imovel::select('categoria')
            ->whereNotNull('categoria')
            ->where('categoria', '!='  , '')
            ->distinct();

        // Aplica busca por termo se fornecido
        if ($search) {
            $query->where('categoria', 'like', "%{$search}%");
        }

        // Opcional: filtrar por user_id se necessário
        $user = Auth::user();
        if ($user && $user->empresa_id) {
            $query->where('user_id', $user->id);
        }

        $categorias = $query
            ->orderBy('categoria')
            ->limit($search ? 15 : 100)
            ->pluck('categoria')
            ->values();

        return response()->json($categorias);
    }

    public function index(IndexImovelRequest $request): Response
    {
        $validated = $request->validated();
        Log::info('ImoveisController@index - filtros validados:', $validated);

        $query = Imovel::with(['imagens', 'listings']);

        // Aplica filtros validados através do scope reutilizável
        $query->filter($validated);

        // Opcional: mostrar apenas imóveis do usuário autenticado
        // Comentado temporariamente para debug - imóveis foram seeded com user_id = 1
        // $user = Auth::user();
        // if ($user && $user->empresa_id) {
        //     // mantemos filtro por user_id caso os anúncios sejam vinculados a vendors
        //     $query->where('user_id', $user->id);
        // }

        // Aplica ordenação padrão se não foi especificada nos filtros
        if (!$request->has('sort')) {
            $query->orderByDesc('created_at');
        }

        $produtos = $query->paginate(15)->withQueryString();

        $collection = $produtos->getCollection()->map(function ($imovel) {
            return [
                'id' => $imovel->id,
                'nome' => $imovel->nome,
                'codigo' => $imovel->codigo ?? null,
                'status' => $imovel->status,
                'condicao' => $imovel->condicao ?? null,
                'descricao' => $imovel->descricao ?? null,
                'cidade' => $imovel->cidade ?? null,
                // nested endereco for frontend helpers
                'endereco' => [
                    'cep' => $imovel->cep ?? null,
                    'endereco' => $imovel->endereco ?? null,
                    'numero' => $imovel->numero ?? null,
                    'complemento' => $imovel->complemento ?? null,
                    'referencia' => $imovel->referencia ?? null,
                    'bairro' => $imovel->bairro ?? null,
                    'cidade' => $imovel->cidade ?? null,
                    'estado' => $imovel->estado ?? null,
                ],
                // valores nested and flat fallback
                'valores' => [
                    'valor_venda' => $imovel->valor_venda ?? null,
                    'valor_locacao' => $imovel->valor_locacao ?? null,
                ],
                'valor_venda' => $imovel->valor_venda ?? null,
                'imageUrl' => $imovel->imagens->first()
                    ? asset('storage/' . $imovel->imagens->first()->imagem_path)
                    : null,
                'listing' => $imovel->listings->first() ? [
                    'id' => $imovel->listings->first()->id,
                    'anuncio_ativo' => (bool) $imovel->listings->first()->anuncio_ativo,
                    'anuncio_status' => $imovel->listings->first()->anuncio_status,
                ] : null,
                'listings' => $imovel->listings->map(function ($listing) {
                    return [
                        'id' => $listing->id,
                        'anuncio_ativo' => (bool) $listing->anuncio_ativo,
                        'anuncio_status' => $listing->anuncio_status,
                        'created_at' => $listing->created_at,
                        'updated_at' => $listing->updated_at,
                    ];
                })->toArray(),
                'listings_count' => $imovel->listings->count(),
                'created_at' => $imovel->created_at ? $imovel->created_at->format('d/m/Y H:i') : null,
            ];
        });

        $produtos->setCollection($collection);

        return Inertia::render('admin/corretor/Imoveis', [
            'produtos' => $produtos,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/corretor/ImoveisCreate');
    }

    public function store(StoreImovelRequest $request)
    {
        try {
            $imovelCreated = null;

            DB::connection('tenant_content')->transaction(function () use ($request, &$imovelCreated) {
                $validated = $request->validated();
                Log::info('StoreImovelRequest validated', ['validated' => $validated]);

                // prepare data only with model fillable keys to avoid mass-assignment issues
                $imovelModel = new Imovel();
                $fillable = method_exists($imovelModel, 'getFillable') ? $imovelModel->getFillable() : [];
                Log::info('Imovel model fillable', ['fillable' => $fillable]);

                $data = array_intersect_key($validated, array_flip($fillable));
                // map frontend 'dormitorios' to DB 'quartos' if present
                if (isset($data['dormitorios']) && !isset($data['quartos'])) {
                    $data['quartos'] = $data['dormitorios'];
                    unset($data['dormitorios']);
                }

                // compatibility: accept old payload key 'tipo' and map to 'categoria'
                if (isset($data['tipo']) && !isset($data['categoria'])) {
                    $data['categoria'] = $data['tipo'];
                    unset($data['tipo']);
                }

                Log::info('Data prepared for Imovel::create', ['data' => $data]);

                // attach current user if model allows
                if (Auth::check() && in_array('user_id', $fillable)) {
                    $data['user_id'] = Auth::id();
                }

                $imovelCreated = Imovel::create($data);
                Log::info('Imovel::create result', ['id' => $imovelCreated->id ?? null, 'exists' => $imovelCreated ? $imovelCreated->exists : false]);

                // always persist proprietario data into imovel_autorizacoes (path nullable)
                $authData = [
                    'imovel_id' => $imovelCreated->id ?? null,
                    'user_id' => Auth::id() ?? null,
                    'path' => null,
                    'proprietario_nome' => $request->input('proprietario_nome'),
                    'proprietario_telefone' => $request->input('proprietario_telefone'),
                    'proprietario_email' => $request->input('proprietario_email'),
                    'proprietario_documento' => $request->input('proprietario_documento'),
                ];

                if ($request->hasFile('autorizacao')) {
                    $file = $request->file('autorizacao');

                    // store in private disk (local) under imoveis/{id}/autorizacoes
                    $extension = $file->getClientOriginalExtension();
                    $uuid = (string) Str::uuid();
                    $filename = $uuid . '.' . $extension;
                    $dir = 'imoveis/' . ($imovelCreated->id ?? 'tmp') . '/autorizacoes';
                    $path = $dir . '/' . $filename;

                    Storage::disk('local')->putFileAs($dir, $file, $filename);

                    // metadata
                    $checksum = null;
                    try {
                        $checksum = hash_file('sha256', $file->getRealPath());
                    } catch (\Throwable $e) {
                        Log::warning('Failed to compute checksum for autorizacao', ['error' => $e->getMessage()]);
                    }

                    $authData['path'] = $path;
                    $authData['original_name'] = $file->getClientOriginalName();
                    $authData['mime_type'] = $file->getClientMimeType() ?: $file->getMimeType();
                    $authData['size'] = $file->getSize();
                    $authData['checksum'] = $checksum;
                    $authData['uploaded_at'] = now();

                    $imovelCreated->autorizacao_venda = true;
                    $imovelCreated->save();
                }

                ImovelAutorizacao::create($authData);
                Log::info('ImovelAutorizacao created', $authData);

                // handle planta upload on create (store in private local disk)
                // handle planta uploads (support multiple)
                if ($request->hasFile('planta')) {
                    $plantas = is_array($request->file('planta')) ? $request->file('planta') : [$request->file('planta')];
                    foreach ($plantas as $file) {
                        if (!$file) continue;
                        $extension = $file->getClientOriginalExtension();
                        $uuid = (string) Str::uuid();
                        $filename = $uuid . '.' . $extension;
                        $dir = 'imoveis/' . ($imovelCreated->id ?? 'tmp') . '/plantas';
                        $path = $dir . '/' . $filename;

                        Storage::disk('local')->putFileAs($dir, $file, $filename);

                        ImovelPlanta::create([
                            'imovel_id' => $imovelCreated->id,
                            'user_id' => Auth::id(),
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]);
                    }
                }

                // handle video uploads (support multiple)
                if ($request->hasFile('videos')) {
                    $videos = is_array($request->file('videos')) ? $request->file('videos') : [$request->file('videos')];
                    foreach ($videos as $file) {
                        if (!$file) continue;
                        $extension = $file->getClientOriginalExtension();
                        $uuid = (string) Str::uuid();
                        $filename = $uuid . '.' . $extension;
                        $dir = 'imoveis/' . ($imovelCreated->id ?? 'tmp') . '/videos';
                        $path = $dir . '/' . $filename;

                        Storage::disk('local')->putFileAs($dir, $file, $filename);

                        ImovelVideo::create([
                            'imovel_id' => $imovelCreated->id,
                            'user_id' => Auth::id(),
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]);
                    }
                }

                // handle imagens uploads (support multiple)
                if ($request->hasFile('imagens')) {
                    $ordem = 1;
                    $imagens = is_array($request->file('imagens')) ? $request->file('imagens') : [$request->file('imagens')];
                    foreach ($imagens as $file) {
                        if (!$file) continue;
                        $path = $file->store('imoveis', 'public');
                        ImovelImagem::create([
                            'imovel_id' => $imovelCreated->id,
                            'user_id' => Auth::id(),
                            'imagem_path' => $path,
                            'ordem' => $ordem,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]);
                        $ordem++;
                    }
                }
            });

            // Reload imovel with relationships for response
            $imovel = Imovel::with('imagens', 'videos', 'plantas', 'autorizacoes')->findOrFail($imovelCreated->id);
            
            return response()->json([
                'message' => 'Imóvel criado com sucesso',
                'imovel' => [
                    'id' => $imovel->id,
                    'nome' => $imovel->nome,
                    'imagens' => $imovel->imagens->map(fn($img) => [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->imagem_path),
                        'ordem' => $img->ordem,
                    ])->toArray(),
                    'videos' => $imovel->videos->map(fn($v) => [
                        'id' => $v->id,
                        'url' => route('admin.corretor.imoveis.video.stream', [$imovel->id, $v->id]),
                        'original_name' => $v->original_name,
                        'size' => $v->size,
                        'mime' => $v->mime_type,
                    ])->toArray(),
                    'plantas' => $imovel->plantas->map(fn($p) => [
                        'id' => $p->id,
                        'original_name' => $p->original_name,
                        'uploaded_at' => $p->uploaded_at->format('d/m/Y H:i') ?? null,
                    ])->toArray(),
                    'autorizacoes' => $imovel->autorizacoes->map(fn($a) => [
                        'id' => $a->id,
                        'original_name' => $a->original_name,
                        'size' => $a->size,
                    ])->toArray(),
                ]
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ImoveisController@store error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Erro ao criar imóvel', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit(int $id): Response
    {
        $imovel = Imovel::findOrFail($id);
        return Inertia::render('admin/corretor/ImoveisCreate', ['produto' => $imovel]);
    }

    public function update(UpdateImovelRequest $request, int $id)
    {
        // CRITICAL: Log RAW request BEFORE validation consumes it
        Log::warning('===== RAW REQUEST DEBUG START =====');
        Log::warning('[RAW] Content-Type header', ['contentType' => $request->header('Content-Type')]);
        Log::warning('[RAW] Request all() keys', ['keys' => array_keys($request->all())]);
        Log::warning('[RAW] Files all() count', ['count' => count($request->files->all())]);
        Log::warning('[RAW] $_FILES superglobal', ['files' => isset($_FILES) ? array_keys($_FILES) : 'EMPTY']);
        Log::warning('[RAW] getContent() length', ['length' => strlen($request->getContent())]);
        Log::warning('===== RAW REQUEST DEBUG END =====');
        
        Log::warning('===== IMOVEL UPDATE START =====');
        Log::warning('Time: ' . now()->toDateTimeString());
        
        $imovel = Imovel::findOrFail($id);

        $data = $request->validated();
        // compatibility: accept old payload key 'tipo' and map to 'categoria'
        if (isset($data['tipo']) && !isset($data['categoria'])) {
            $data['categoria'] = $data['tipo'];
            unset($data['tipo']);
        }

        // Strip file/media fields from $data — they are processed separately below
        unset($data['imagens'], $data['videos'], $data['planta'], $data['autorizacao']);
        
        // Debug: Log incoming files with EXTREME detail
        Log::warning('[ImovelUpdate] Request received at ' . now()->toDateTimeString(), [
            'imovel_id' => $id,
            'imovel_exists' => $imovel ? 'YES' : 'NO',
            'request_method' => $request->getMethod(),
            'wants_json' => $request->wantsJson(),
            'ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'has_imagens' => $request->hasFile('imagens') ? 'YES' : 'NO',
            'has_videos' => $request->hasFile('videos') ? 'YES' : 'NO',
            'has_planta' => $request->hasFile('planta') ? 'YES' : 'NO',
            'has_autorizacao' => $request->hasFile('autorizacao') ? 'YES' : 'NO',
            'imagens_count' => count((array)$request->file('imagens')),
            'videos_count' => count((array)$request->file('videos')),
            'planta_count' => count((array)$request->file('planta')),
            'autorizacao_count' => $request->hasFile('autorizacao') ? 1 : 0,
            'database_connection' => DB::getDefaultConnection(),
        ]);
        
        try {
            Log::warning('[ImovelUpdate] Starting DB transaction');
            DB::transaction(function () use ($request, $data, $imovel) {
                Log::warning('[ImovelUpdate] ✅ Transaction started for imovel_id: ' . $imovel->id);
                $imovel->update($data);
                Log::warning('[ImovelUpdate] ✅ Imovel record updated');

                // Pre-validate file sizes before storage
                $maxVideoSize = 1 * 1024 * 1024 * 1024; // 1GB
                $maxPlantaSize = 10 * 1024 * 1024; // 10MB
                $maxImageSize = 5 * 1024 * 1024; // 5MB
                $maxAuthSize = 5 * 1024 * 1024; // 5MB

                if ($request->hasFile('videos')) {
                    $videos = is_array($request->file('videos')) ? $request->file('videos') : [$request->file('videos')];
                    foreach ($videos as $file) {
                        if (!$file) continue;
                        if ($file->getSize() > $maxVideoSize) {
                            throw new \Exception("Vídeo exceeds maximum size of 1GB. File size: " . round($file->getSize() / (1024 * 1024), 2) . "MB");
                        }
                    }
                }

                if ($request->hasFile('planta')) {
                    $plantas = is_array($request->file('planta')) ? $request->file('planta') : [$request->file('planta')];
                    foreach ($plantas as $file) {
                        if (!$file) continue;
                        if ($file->getSize() > $maxPlantaSize) {
                            throw new \Exception("Planta exceeds maximum size of 10MB. File size: " . round($file->getSize() / (1024 * 1024), 2) . "MB");
                        }
                    }
                }

                if ($request->hasFile('imagens')) {
                    $imagens = is_array($request->file('imagens')) ? $request->file('imagens') : [$request->file('imagens')];
                    foreach ($imagens as $file) {
                        if (!$file) continue;
                        if ($file->getSize() > $maxImageSize) {
                            throw new \Exception("Imagem exceeds maximum size of 5MB. File size: " . round($file->getSize() / (1024 * 1024), 2) . "MB");
                        }
                    }
                }

                if ($request->hasFile('autorizacao')) {
                    $file = $request->file('autorizacao');
                    if ($file && $file->getSize() > $maxAuthSize) {
                        throw new \Exception("Autorização exceeds maximum size of 5MB. File size: " . round($file->getSize() / (1024 * 1024), 2) . "MB");
                    }
                }

                // handle planta uploads (support multiple)
                if ($request->hasFile('planta')) {
                    Log::info('[ImovelUpdate] Processing plantas');
                    $plantas = is_array($request->file('planta')) ? $request->file('planta') : [$request->file('planta')];
                    Log::info('[ImovelUpdate] Plantas count: ' . count($plantas));
                    foreach ($plantas as $idx => $file) {
                        if (!$file) {
                            Log::warning('[ImovelUpdate] Planta ' . $idx . ' is empty');
                            continue;
                        }
                        $extension = $file->getClientOriginalExtension();
                        $uuid = (string) Str::uuid();
                        $filename = $uuid . '.' . $extension;
                        $dir = 'imoveis/' . $imovel->id . '/plantas';
                        $path = $dir . '/' . $filename;
                        
                        Log::info('[ImovelUpdate] Storing planta ' . $idx, [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'path' => $path
                        ]);

                        Storage::disk('local')->putFileAs($dir, $file, $filename);

                        ImovelPlanta::create([
                            'imovel_id' => $imovel->id,
                            'user_id' => Auth::id(),
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]);
                    }
                }

                // handle video uploads (support multiple)
                if ($request->hasFile('videos')) {
                    Log::info('[ImovelUpdate] Processing videos');
                    $videos = is_array($request->file('videos')) ? $request->file('videos') : [$request->file('videos')];
                    Log::info('[ImovelUpdate] Videos count: ' . count($videos));
                    foreach ($videos as $idx => $file) {
                        if (!$file) {
                            Log::warning('[ImovelUpdate] Video ' . $idx . ' is empty');
                            continue;
                        }
                        $extension = $file->getClientOriginalExtension();
                        $uuid = (string) Str::uuid();
                        $filename = $uuid . '.' . $extension;
                        $dir = 'imoveis/' . $imovel->id . '/videos';
                        $path = $dir . '/' . $filename;
                        
                        Log::info('[ImovelUpdate] Storing video ' . $idx, [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'path' => $path
                        ]);

                        Storage::disk('local')->putFileAs($dir, $file, $filename);

                        ImovelVideo::create([
                            'imovel_id' => $imovel->id,
                            'user_id' => Auth::id(),
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]);
                    }
                }

                // handle authorization: replace existing record/file or create if missing
                Log::info('[ImovelUpdate] Processing autorizacao');
                $existingAuth = ImovelAutorizacao::where('imovel_id', $imovel->id)->orderByDesc('created_at')->first();
                if ($request->hasFile('autorizacao')) {
                    Log::info('[ImovelUpdate] Has autorizacao file');
                    $file = $request->file('autorizacao');

                    // store new file in private disk
                    $extension = $file->getClientOriginalExtension();
                    $uuid = (string) Str::uuid();
                    $filename = $uuid . '.' . $extension;
                    $dir = 'imoveis/' . $imovel->id . '/autorizacoes';
                    $path = $dir . '/' . $filename;
                    
                    Log::info('[ImovelUpdate] Storing autorizacao', [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'path' => $path
                    ]);

                    Storage::disk('local')->putFileAs($dir, $file, $filename);

                    // compute checksum safely
                    $checksum = null;
                    try {
                        $checksum = hash_file('sha256', $file->getRealPath());
                    } catch (\Throwable $e) {
                        Log::warning('Failed to compute checksum for autorizacao', ['error' => $e->getMessage()]);
                    }

                    $authData = [
                        'imovel_id' => $imovel->id,
                        'user_id' => Auth::id(),
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                        'size' => $file->getSize(),
                        'checksum' => $checksum,
                        'uploaded_at' => now(),
                        'proprietario_nome' => $request->input('proprietario_nome'),
                        'proprietario_telefone' => $request->input('proprietario_telefone'),
                        'proprietario_email' => $request->input('proprietario_email'),
                        'proprietario_documento' => $request->input('proprietario_documento'),
                    ];

                    if ($existingAuth) {
                        // attempt to delete old file from both public and local (backwards compatibility)
                        if ($existingAuth->path) {
                            try { Storage::disk('public')->delete($existingAuth->path); } catch (\Throwable $e) { Log::warning('Failed to delete old autorizacao file on public disk', ['path' => $existingAuth->path, 'error' => $e->getMessage()]); }
                            try { Storage::disk('local')->delete($existingAuth->path); } catch (\Throwable $e) { Log::warning('Failed to delete old autorizacao file on local disk', ['path' => $existingAuth->path, 'error' => $e->getMessage()]); }
                        }
                        $existingAuth->update($authData);
                        Log::info('ImovelAutorizacao replaced', ['id' => $existingAuth->id, 'data' => $authData]);
                    } else {
                        ImovelAutorizacao::create($authData);
                        Log::info('ImovelAutorizacao created', $authData);
                    }

                    $imovel->autorizacao_venda = true;
                    $imovel->save();
                } else {
                    // no file uploaded — ensure proprietor fields are saved/updated
                    $proprietarioData = [
                        'proprietario_nome' => $request->input('proprietario_nome'),
                        'proprietario_telefone' => $request->input('proprietario_telefone'),
                        'proprietario_email' => $request->input('proprietario_email'),
                        'proprietario_documento' => $request->input('proprietario_documento'),
                    ];
                    if ($existingAuth) {
                        $existingAuth->update($proprietarioData);
                        Log::info('ImovelAutorizacao updated proprietario fields', ['id' => $existingAuth->id, 'data' => $proprietarioData]);
                    } else {
                        $createData = array_merge(['imovel_id' => $imovel->id, 'user_id' => Auth::id(), 'path' => null], $proprietarioData);
                        ImovelAutorizacao::create($createData);
                        Log::info('ImovelAutorizacao created (no file)', $createData);
                    }
                }

                // append imagens preserving ordem
                if ($request->hasFile('imagens')) {
                    Log::warning('[ImovelUpdate] ✅ Processing imagens');
                    $startOrder = ImovelImagem::where('imovel_id', $imovel->id)->max('ordem');
                    $order = is_null($startOrder) ? 0 : ($startOrder + 1);
                    $imagens = is_array($request->file('imagens')) ? $request->file('imagens') : [$request->file('imagens')];
                    Log::warning('[ImovelUpdate] Imagens count: ' . count($imagens));
                    foreach ($imagens as $idx => $file) {
                        if (!$file) {
                            Log::warning('[ImovelUpdate] ❌ Imagem ' . $idx . ' is empty');
                            continue;
                        }
                        Log::warning('[ImovelUpdate] ✅ Storing imagem ' . $idx, [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'mime' => $file->getClientMimeType()
                        ]);
                        $path = $file->store('imoveis', 'public');
                        Log::warning('[ImovelUpdate] ✅ Imagem ' . $idx . ' stored at: ' . $path);
                        
                        $created = ImovelImagem::create([
                            'imovel_id' => $imovel->id,
                            'user_id' => Auth::id(),
                            'imagem_path' => $path,
                            'ordem' => $order++,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]);
                        Log::warning('[ImovelUpdate] ✅ Imagem ' . $idx . ' created in DB with id: ' . $created->id);
                    }
                } else {
                    Log::warning('[ImovelUpdate] No imagens in request');
                }
            });
            Log::warning('[ImovelUpdate] ✅ Transaction completed successfully at ' . now()->toDateTimeString());
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ ImoveisController@update validation failed', ['errors' => $e->errors()]);
            throw $e; // Laravel handles FormRequest validation automatically
        } catch (\Exception $e) {
            Log::error('❌ ImoveisController@update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            // Inertia requests: redirect back with error
            if ($request->header('X-Inertia')) {
                return redirect()->back()->withErrors(['media' => $e->getMessage()]);
            }

            // Return structured JSON error for AJAX/API requests
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'error' => 'Erro ao processar upload de mídia',
                    'message' => $e->getMessage(),
                    'details' => config('app.debug') ? $e->getMessage() : null
                ], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::error('❌ ImoveisController@update critical error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }

        // Inertia requests: redirect to show page (Inertia will follow and re-render with fresh props)
        if ($request->header('X-Inertia')) {
            return redirect()->route('admin.corretor.imoveis.show', $id);
        }

        // Non-Inertia AJAX/JSON requests: return JSON response
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            Log::warning('[ImovelUpdate] Refreshing imovel data from database');
            $imovel->refresh();
            $imovel->load('imagens', 'videos', 'plantas', 'autorizacoes');
            
            Log::warning('[ImovelUpdate] ✅ Returning JSON response with imovel data', [
                'imovel_id' => $imovel->id,
                'imagens_count' => count($imovel->imagens),
                'videos_count' => count($imovel->videos),
                'plantas_count' => count($imovel->plantas),
                'autorizacoes_count' => count($imovel->autorizacoes),
            ]);

            return response()->json([
                'imovel' => [
                    'id' => $imovel->id,
                    'codigo' => $imovel->codigo,
                    'nome' => $imovel->nome,
                    'status' => $imovel->status,
                    'descricao' => $imovel->descricao,
                    'condicao' => $imovel->condicao,
                    'cidade' => $imovel->cidade,
                    'endereco' => [
                        'cep' => $imovel->cep,
                        'endereco' => $imovel->endereco,
                        'numero' => $imovel->numero,
                        'complemento' => $imovel->complemento,
                        'referencia' => $imovel->referencia,
                        'bairro' => $imovel->bairro,
                        'cidade' => $imovel->cidade,
                        'estado' => $imovel->estado,
                    ],
                    'valores' => [
                        'valor_venda' => $imovel->valor_venda,
                        'valor_locacao' => $imovel->valor_locacao,
                    ],
                    'imagens' => $imovel->imagens->map(fn($img) => [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->imagem_path),
                        'ordem' => $img->ordem,
                        'original_name' => $img->original_name ?? basename($img->imagem_path ?? ''),
                    ])->toArray(),
                    'videos' => $imovel->videos->map(fn($v) => [
                        'id' => $v->id,
                        'url' => route('admin.corretor.imoveis.video.stream', [$imovel->id, $v->id]),
                        'original_name' => $v->original_name,
                        'size' => $v->size,
                        'mime' => $v->mime_type,
                    ])->toArray(),
                    'plantas' => $imovel->plantas->map(fn($p) => [
                        'id' => $p->id,
                        'original_name' => $p->original_name,
                        'uploaded_at' => $p->uploaded_at ? $p->uploaded_at->format('d/m/Y H:i') : null,
                    ])->toArray(),
                    'autorizacoes' => $imovel->autorizacoes->map(fn($a) => [
                        'id' => $a->id,
                        'original_name' => $a->original_name,
                        'size' => $a->size,
                    ])->toArray(),
                ]
            ]);
        }

        return redirect()->route('admin.corretor.imoveis.show', $id)->with('success', 'Imóvel atualizado.');
    }

    public function destroy(int $id)
    {
        try {
            $imovel = Imovel::findOrFail($id);
            
            // Deletar anúncios (listings) vinculados
            $imovel->listings()->delete();
            
            // Deletar o imóvel
            $imovel->delete();
            
            return response()->json(['message' => 'Imóvel e anúncios associados foram removidos com sucesso.'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar imóvel: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao deletar imóvel: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, int $id): Response|\Illuminate\Http\JsonResponse
    {
        $with = ['imagens', 'plantas', 'videos', 'autorizacoes', 'user', 'listings'];
        if (Schema::connection('tenant_content')->hasTable('imovel_midias')) {
            $with[] = 'midias';
        }

        $imovel = Imovel::with($with)->findOrFail($id);

        // build payload
        $imagens = $imovel->imagens->map(function ($img) {
            return [
                'id' => $img->id,
                'ordem' => $img->ordem,
                'url' => $img->imagem_path ? asset('storage/' . $img->imagem_path) : null,
                'original_name' => $img->original_name ?? basename($img->imagem_path ?? ''),
            ];
        })->toArray();

        $plantas = $imovel->plantas->map(function ($p) use ($imovel) {
            $url = $p->path ? route('admin.corretor.imoveis.planta.download', [$imovel->id, $p->id]) : null;
            $ext = strtolower(pathinfo($p->path ?? '', PATHINFO_EXTENSION));
            $type = 'other';
            if (in_array($ext, ['png','jpg','jpeg','gif','webp','svg'])) $type = 'image';
            if ($ext === 'pdf') $type = 'pdf';
            $mime = null;
            try { if ($p->path && Storage::disk('local')->exists($p->path)) $mime = Storage::disk('local')->mimeType($p->path); } catch (\Throwable $e) { /* ignore */ }

            return [
                'id' => $p->id,
                'url' => $url,
                'path' => $p->path,
                'type' => $type,
                'mime' => $mime,
                'original_name' => $p->original_name ?? basename($p->path ?? ''),
                'mime_type' => $mime,
            ];
        })->toArray();

        $videos = $imovel->videos->map(function ($v) use ($imovel) {
            $url = $v->path ? route('admin.corretor.imoveis.video.stream', [$imovel->id, $v->id]) : null;
            $ext = strtolower(pathinfo($v->path ?? '', PATHINFO_EXTENSION));
            $type = in_array($ext, ['mp4','webm','mov','avi']) ? 'video' : 'other';
            $mime = null;
            try { if ($v->path && Storage::disk('local')->exists($v->path)) $mime = Storage::disk('local')->mimeType($v->path); } catch (\Throwable $e) { /* ignore */ }

            return [
                'id' => $v->id,
                'url' => $url,
                'path' => $v->path,
                'type' => $type,
                'mime' => $mime,
                'original_name' => $v->original_name ?? basename($v->path ?? ''),
                'mime_type' => $mime,
            ];
        })->toArray();

        $midias = [];
        if (Schema::connection('tenant_content')->hasTable('imovel_midias')) {
            $midias = $imovel->midias->map(function ($m) {
                return [
                    'id' => $m->id,
                    'tipo' => $m->tipo,
                    'ordem' => $m->ordem,
                    'url' => $m->path ? asset('storage/' . $m->path) : null,
                ];
            })->toArray();
        }

        $autorizacoes = $imovel->autorizacoes->map(function ($a) use ($imovel) {
            // normalize uploaded_at and created_at to strings safely
            $uploadedAt = null;
            if (!empty($a->uploaded_at)) {
                $uploadedAt = $a->uploaded_at instanceof \DateTimeInterface ? $a->uploaded_at->toDateTimeString() : (string) $a->uploaded_at;
            } elseif (!empty($a->created_at)) {
                $uploadedAt = $a->created_at instanceof \DateTimeInterface ? $a->created_at->toDateTimeString() : (string) $a->created_at;
            }

            return [
                'id' => $a->id,
                'original_name' => $a->original_name ?? basename($a->path ?? ''),
                'mime_type' => $a->mime_type ?? null,
                'size' => $a->size ?? null,
                'uploaded_at' => $uploadedAt,
                'downloadUrl' => route('admin.corretor.imoveis.autorizacao.download', [$imovel->id, $a->id]),
            ];
        })->toArray();

        $payload = [
            'id' => $imovel->id,
            'codigo' => $imovel->codigo,
            'nome' => $imovel->nome,
            'status' => $imovel->status,
            'descricao' => $imovel->descricao,
            'categoria' => $imovel->categoria,
            'finalidade' => $imovel->finalidade,
            'modalidade' => $imovel->modalidade,
            'condicao' => $imovel->condicao,
            'exclusividade' => $imovel->exclusividade,
            'endereco' => [
                'cep' => $imovel->cep,
                'endereco' => $imovel->endereco,
                'numero' => $imovel->numero,
                'bairro' => $imovel->bairro,
                'cidade' => $imovel->cidade,
                'estado' => $imovel->estado,
                'complemento' => $imovel->complemento,
                'referencia' => $imovel->referencia,
                'andar' => $imovel->andar,
                'torre' => $imovel->torre,
                'mostrar_endereco_completo' => $imovel->mostrar_endereco_completo,
            ],
            'valores' => [
                'valor_venda' => $imovel->valor_venda,
                'valor_locacao' => $imovel->valor_locacao,
                'valor_condominio' => $imovel->valor_condominio,
                'valor_iptu' => $imovel->valor_iptu,
                'gas' => $imovel->gas,
                'luz' => $imovel->luz,
                'aceita_financiamento' => $imovel->aceita_financiamento,
                'aceita_permuta' => $imovel->aceita_permuta,
                'comissao_percent' => $imovel->comissao_percent,
                'comissao_valor' => $imovel->comissao_valor,
            ],
            'caracteristicas' => [
                'quartos' => $imovel->quartos,
                'suites' => $imovel->suites,
                'banheiros' => $imovel->banheiros,
                'vagas' => $imovel->vagas,
                'salas' => $imovel->salas,
                'area_construida' => $imovel->area_construida,
                'area_util' => $imovel->area_util,
                'area_total' => $imovel->area_total,
                'area_terreno_largura' => $imovel->area_terreno_largura,
                'area_terreno_comprimento' => $imovel->area_terreno_comprimento,
                'ano_construcao' => $imovel->ano_construcao,
                'mobilia' => $imovel->mobilia,
                'varanda' => $imovel->varanda,
                'areas_lazer' => $imovel->areas_lazer,
                'itens' => $imovel->itens,
            ],
            'imagens' => $imagens,
            'plantas' => $plantas,
            'videos' => $videos,
            'midias' => $midias,
            'autorizacoes' => $autorizacoes,
            'proprietario' => [
                'nome' => $imovel->proprietario_nome,
                'telefone' => $imovel->proprietario_telefone,
                'email' => $imovel->proprietario_email,
                'documento' => $imovel->proprietario_documento,
            ],
            'created_at' => $imovel->created_at ? $imovel->created_at->format('d/m/Y H:i') : null,
        ];

        // include listing info if available
        $payload['listings'] = $imovel->listings->map(fn($l) => [
            'id' => $l->id,
            'anuncio_ativo' => (bool) $l->anuncio_ativo,
            'anuncio_status' => $l->anuncio_status,
        ])->toArray();
        // backward compat: keep single listing reference
        $firstListing = $imovel->listings->first();
        if ($firstListing) {
            $payload['listing'] = [
                'id' => $firstListing->id,
                'anuncio_ativo' => (bool) $firstListing->anuncio_ativo,
                'anuncio_status' => $firstListing->anuncio_status,
            ];
        }

        // Retorna JSON apenas para requisições AJAX genuínas (do modal de detalhes)
        // Não retorna JSON para navegações Inertia (que têm header X-Inertia)
        $isInertiaRequest = $request->header('X-Inertia');
        if (!$isInertiaRequest && ($request->wantsJson() || $request->ajax() || $request->expectsJson())) {
            return response()->json(['imovel' => $payload]);
        }

        return Inertia::render('admin/corretor/ImovelShow', [
            'imovel' => $payload,
        ]);
    }

    /**
     * Download an autorizacao file for an imovel (protected).
     */
    public function downloadAutorizacao(Request $request, int $imovelId, int $authId)
    {
        $aut = ImovelAutorizacao::findOrFail($authId);
        if ($aut->imovel_id != $imovelId) {
            abort(404);
        }

        $imovel = Imovel::findOrFail($imovelId);

        // basic permission: owner or uploader
        $userId = Auth::id();
        if ($userId !== $imovel->user_id && $userId !== $aut->user_id) {
            abort(403);
        }

        $path = $aut->path;

        // prefer local private disk
        if (Storage::disk('local')->exists($path)) {
            $name = $aut->original_name ?? basename($path);
            return Storage::disk('local')->download($path, $name);
        }

        // fallback to public disk
        if (Storage::disk('public')->exists($path)) {
            $name = $aut->original_name ?? basename($path);
            return Storage::disk('public')->download($path, $name);
        }

        abort(404);
    }

    /**
     * Serve a planta (PDF/image) for an imovel (protected, inline display).
     */
    public function downloadPlanta(Request $request, int $imovelId, int $plantaId)
    {
        $planta = ImovelPlanta::findOrFail($plantaId);
        if ($planta->imovel_id != $imovelId) {
            abort(404);
        }

        $imovel = Imovel::findOrFail($imovelId);

        // basic permission: owner or uploader
        $userId = Auth::id();
        if ($userId !== $imovel->user_id && $userId !== $planta->user_id) {
            abort(403);
        }

        $path = $planta->path;

        if (Storage::disk('local')->exists($path)) {
            $full = Storage::disk('local')->path($path);
            $mime = null;
            try { $mime = Storage::disk('local')->mimeType($path); } catch (\Throwable $e) { /* ignore */ }
            $mime = $mime ?: 'application/octet-stream';
            return response()->file($full, ['Content-Type' => $mime, 'Content-Disposition' => 'inline; filename="' . basename($path) . '"']);
        }

        if (Storage::disk('public')->exists($path)) {
            $full = Storage::disk('public')->path($path);
            $mime = null;
            try { $mime = Storage::disk('public')->mimeType($path); } catch (\Throwable $e) { /* ignore */ }
            $mime = $mime ?: 'application/octet-stream';
            return response()->file($full, ['Content-Type' => $mime, 'Content-Disposition' => 'inline; filename="' . basename($path) . '"']);
        }

        abort(404);
    }

    /**
     * Stream a video for an imovel (protected, inline display).
     */
    public function streamVideo(Request $request, int $imovelId, int $videoId)
    {
        $video = ImovelVideo::findOrFail($videoId);
        if ($video->imovel_id != $imovelId) {
            abort(404);
        }

        $imovel = Imovel::findOrFail($imovelId);

        // basic permission: owner or uploader
        $userId = Auth::id();
        if ($userId !== $imovel->user_id && $userId !== $video->user_id) {
            abort(403);
        }

        $path = $video->path;

        if (Storage::disk('local')->exists($path)) {
            $full = Storage::disk('local')->path($path);
            $mime = null;
            try { $mime = Storage::disk('local')->mimeType($path); } catch (\Throwable $e) { /* ignore */ }
            $mime = $mime ?: 'video/mp4';
            return response()->file($full, ['Content-Type' => $mime, 'Content-Disposition' => 'inline; filename="' . basename($path) . '"']);
        }

        if (Storage::disk('public')->exists($path)) {
            $full = Storage::disk('public')->path($path);
            $mime = null;
            try { $mime = Storage::disk('public')->mimeType($path); } catch (\Throwable $e) { /* ignore */ }
            $mime = $mime ?: 'video/mp4';
            return response()->file($full, ['Content-Type' => $mime, 'Content-Disposition' => 'inline; filename="' . basename($path) . '"']);
        }

        abort(404);
    }

    /**
     * Delete a single image from an imovel
     */
    public function deleteImage(Request $request, int $imovelId, int $imageId)
    {
        $image = ImovelImagem::findOrFail($imageId);
        if ($image->imovel_id != $imovelId) {
            abort(404);
        }

        $imovel = Imovel::findOrFail($imovelId);
        $userId = Auth::id();

        // Permission check
        if ($userId !== $imovel->user_id && $userId !== $image->user_id) {
            abort(403);
        }

        // Delete file from storage
        try {
            Storage::disk('public')->delete($image->imagem_path);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete image file', ['path' => $image->imagem_path, 'error' => $e->getMessage()]);
        }

        // Delete database record
        $image->delete();

        // Reload and return updated media
        $imovel->load('imagens', 'videos', 'plantas', 'autorizacoes');

        if ($request->header('X-Inertia')) {
            return redirect()->route('admin.corretor.imoveis.show', $imovelId);
        }

        return response()->json([
            'imovel' => [
                'imagens' => $imovel->imagens->map(fn($img) => [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->imagem_path),
                    'ordem' => $img->ordem,
                ])->toArray(),
            ]
        ]);
    }

    /**
     * Delete a single video from an imovel
     */
    public function deleteVideo(Request $request, int $imovelId, int $videoId)
    {
        $video = ImovelVideo::findOrFail($videoId);
        if ($video->imovel_id != $imovelId) {
            abort(404);
        }

        $imovel = Imovel::findOrFail($imovelId);
        $userId = Auth::id();

        // Permission check
        if ($userId !== $imovel->user_id && $userId !== $video->user_id) {
            abort(403);
        }

        // Delete file from storage
        try {
            Storage::disk('local')->delete($video->path);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete video file', ['path' => $video->path, 'error' => $e->getMessage()]);
        }

        // Delete database record
        $video->delete();

        // Reload and return updated media
        $imovel->load('imagens', 'videos', 'plantas', 'autorizacoes');

        if ($request->header('X-Inertia')) {
            return redirect()->route('admin.corretor.imoveis.show', $imovelId);
        }

        return response()->json([
            'imovel' => [
                'videos' => $imovel->videos->map(fn($v) => [
                    'id' => $v->id,
                    'original_name' => $v->original_name,
                    'size' => $v->size,
                ])->toArray(),
            ]
        ]);
    }

    /**
     * Delete a single planta from an imovel
     */
    public function deletePlanta(Request $request, int $imovelId, int $plantaId)
    {
        $planta = ImovelPlanta::findOrFail($plantaId);
        if ($planta->imovel_id != $imovelId) {
            abort(404);
        }

        $imovel = Imovel::findOrFail($imovelId);
        $userId = Auth::id();

        // Permission check
        if ($userId !== $imovel->user_id && $userId !== $planta->user_id) {
            abort(403);
        }

        // Delete file from storage
        try {
            Storage::disk('local')->delete($planta->path);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete planta file', ['path' => $planta->path, 'error' => $e->getMessage()]);
        }

        // Delete database record
        $planta->delete();

        // Reload and return updated media
        $imovel->load('imagens', 'videos', 'plantas', 'autorizacoes');

        if ($request->header('X-Inertia')) {
            return redirect()->route('admin.corretor.imoveis.show', $imovelId);
        }

        return response()->json([
            'imovel' => [
                'plantas' => $imovel->plantas->map(fn($p) => [
                    'id' => $p->id,
                    'original_name' => $p->original_name,
                    'uploaded_at' => $p->uploaded_at ? $p->uploaded_at->format('d/m/Y H:i') : null,
                ])->toArray(),
            ]
        ]);
    }

    /**
     * Delete an autorizacao from an imovel
     */
    public function deleteAutorizacao(Request $request, int $imovelId, int $authId)
    {
        $autorizacao = ImovelAutorizacao::findOrFail($authId);
        if ($autorizacao->imovel_id != $imovelId) {
            abort(404);
        }

        $imovel = Imovel::findOrFail($imovelId);
        $userId = Auth::id();

        // Permission check
        if ($userId !== $imovel->user_id && $userId !== $autorizacao->user_id) {
            abort(403);
        }

        // Delete file from storage
        try {
            Storage::disk('local')->delete($autorizacao->path);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete autorizacao file', ['path' => $autorizacao->path, 'error' => $e->getMessage()]);
        }

        // Delete database record
        $autorizacao->delete();

        // Reload and return updated media
        $imovel->load('imagens', 'videos', 'plantas', 'autorizacoes');

        if ($request->header('X-Inertia')) {
            return redirect()->route('admin.corretor.imoveis.show', $imovelId);
        }

        return response()->json([
            'imovel' => [
                'autorizacoes' => $imovel->autorizacoes->map(fn($a) => [
                    'id' => $a->id,
                    'original_name' => $a->original_name,
                    'size' => $a->size,
                ])->toArray(),
            ]
        ]);
    }
}