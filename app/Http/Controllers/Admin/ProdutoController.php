<?php

namespace App\Http\Controllers\Admin;

use App\Models\Produto;
use App\Models\ProdutoImagem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tamanho;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Helpers\NumberHelper;


class ProdutoController extends Controller
{
    public function index()
    {
        $products = Produto::with('imagens')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'nome' => $product->nome,
                'descricao' => $product->descricao,
                'estoque' => $product->estoque,
                'tamanhos' => $product->tamanhos,
                'imageUrl' => $product->imagens->first()
                    ? asset('storage/' . $product->imagens->first()->imagem_path)
                    : null,
                 'created_at' => $product->created_at
                    ? $product->created_at->format('d/m/Y H:i')
                    : null,
                
            ];
        });
        
        return Inertia::render('admin/ecommerce/produtos/ProdutosConfig')->with('products', $products);

    }

    public function create()
    {
        return Inertia::render('admin/ecommerce/produtos/AdicionarProduto');
    }

 public function store(Request $request)
    {
        // 1. Validação do request.
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'estoque' => 'required|integer|min:0',
            'tamanhos' => 'required|array',
            'tamanhos.*.nome' => 'required|string|max:255',
            'tamanhos.*.preco' => 'required|string|min:0',
            'imagens.*' => 'nullable|image|max:2048'
        ]);

        // 2. Criação do produto principal
        $produto = new Produto();
        $produto->user_id = auth()->id();
        $produto->nome = $validated['nome'];
        $produto->descricao = $validated['descricao'];
        $produto->estoque = $validated['estoque'];
        $produto->save();

        // 3. Associar os tamanhos e preços (a parte crucial!)
        foreach ($validated['tamanhos'] as $tamanhoData) {
            $tamanho = Tamanho::firstOrCreate(['nome' => $tamanhoData['nome']]);

            $produto->tamanhos()->attach($tamanho->id, [
                'preco' => NumberHelper::parseMoney($tamanhoData['preco']),
            ]);
        }


        // 4. Salvar as imagens (sua lógica atual, que parece correta)
        if ($request->hasFile('imagens')) {
            $ordem = 1;
            foreach ($request->file('imagens') as $imagem) {
                $path = $imagem->store('produtos', 'public');
                ProdutoImagem::create([
                    'produto_id' => $produto->id,
                    'user_id' => auth()->id(),
                    'imagem_path' => $path,
                    'ordem' => $ordem,
                ]);
                $ordem++;
            }
        }

        return Inertia::location(route('admin.ecommerce.produtos.config'));
    }


public function edit($id)
{
    $product = Produto::with(['imagens', 'tamanhos'])->findOrFail($id);
    $productArray = $product->toArray();

    // Retorna array com id e url da imagem
    $imagensComId = array_map(function ($imagem) {
        return [
            'id' => $imagem['id'],
            'url' => asset('storage/' . $imagem['imagem_path']),
        ];
    }, $productArray['imagens']);

    $productTamanhos = $product->tamanhos->map(function ($tamanho) {
        return [
            'id' => $tamanho->id,
            'nome' => $tamanho->nome,
            'preco' => $tamanho->pivot->preco,
        ];
    })->toArray();

    $productArray['imagens'] = $imagensComId;

    return Inertia::render('admin/ecommerce/produtos/EditarProduto', [
        'products' => $productArray,
        'imagemPaths' => $imagensComId,
        'productTamanhos' => $productTamanhos,
    ]);
}


    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        $produto->update($request->all());
        
        $tamanhos = $request->input('tamanhos', []);

        if (is_string($tamanhos)) {
            $tamanhos = json_decode($tamanhos, true);
        }

        $tamanhosSync = [];

        foreach ($tamanhos as $tamanhoData) {
            $tamanho = Tamanho::firstOrCreate(['nome' => $tamanhoData['nome']]);
            $tamanhosSync[$tamanho->id] = [
                'preco' => NumberHelper::parseMoney($tamanhoData['preco']),
            ];
        }

        // Sincroniza tamanhos: adiciona novos, atualiza e remove os que não estão no array
        $produto->tamanhos()->sync($tamanhosSync);


        $imagensExistentes = json_decode($request->input('imagensExistentes', '[]'), true);

        $idsParaManter = [];

        if (!empty($imagensExistentes)) {
            if (isset($imagensExistentes[0]) && is_array($imagensExistentes[0]) && isset($imagensExistentes[0]['id'])) {
                $idsParaManter = array_map(fn($img) => $img['id'], $imagensExistentes);
            } else {
                $idsParaManter = $imagensExistentes;
            }
        }

        ProdutoImagem::where('produto_id', $produto->id)
            ->whereNotIn('id', $idsParaManter)
            ->delete();

        // Descobre a maior ordem atual
        $maxOrdem = ProdutoImagem::where('produto_id', $produto->id)->max('ordem');
        $maxOrdem = $maxOrdem ?? 0; // se null, começa em zero

        // Salva as imagens novas com a ordem correta
        $ordemAtual = $maxOrdem;
        if ($request->hasFile('imagensNovas')) {
            foreach ($request->file('imagensNovas') as $imagem) {
                $path = $imagem->store('produtos', 'public');
                $ordemAtual++;

                ProdutoImagem::create([
                    'produto_id' => $produto->id,
                    'user_id' => auth()->id(),
                    'imagem_path' => $path,
                    'ordem' => $ordemAtual,
                ]);
            }
        }

        return Inertia::location(route('admin.ecommerce.produtos.config'));
    }

    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->delete();

        return redirect()->route('admin.ecommerce.produtos.config');
    }

    public function anuncio($id)
    {
        $produto = Produto::with(['imagens', 'tamanhos'])->findOrFail($id);

        $produtosRelacionados = Produto::with(['imagens', 'tamanhos'])
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'tamanhos' => $p->tamanhos, // passe os tamanhos para o front
                    'imageUrl' => $p->imagens->first()
                        ? asset('storage/' . $p->imagens->first()->imagem_path)
                        : null,
                ];
            });

        return Inertia::render('Anuncio', [
            'produto' => $produto,
            'produtoSwiper' => $produtosRelacionados,
        ]);
    }

    public function show(){
        $produtos = Produto::with(['imagens', 'tamanhos'])->get()->map(function ($produto) {

            // Mapeia os dados da relação `tamanhos` para uma estrutura limpa
            $tamanhosData = $produto->tamanhos->map(function ($tamanho) {
                return [
                    'id' => $tamanho->id,
                    'nome' => $tamanho->nome,
                    'preco' => $tamanho->pivot->preco, // Acessa o preço da tabela intermediária
                ];
            });

            return [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'descricao' => $produto->descricao,
                'estoque' => $produto->estoque,
                'tamanhos' => $tamanhosData,
                'imageUrl' => $produto->imagens->first()
                    ? asset('storage/' . $produto->imagens->first()->imagem_path)
                    : null,
                'created_at' => $produto->created_at->format('d/m/Y H:i'),
            ];
        });
            Log::info('Produtos:', $produtos->toArray()); // Registra no log
        return Inertia::render('Produtos', [
            'produtoSwiper' => $produtos,
        ]);
    }
    
}
