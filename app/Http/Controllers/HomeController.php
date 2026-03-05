<?php

namespace App\Http\Controllers;
use App\Models\Produto;
use Inertia\Inertia;

class HomeController extends Controller
{    /**
     * Base controller method for demonstration purposes.
     *
     * @return string
     */
    public function index(){
        // Carrega as relações `imagens` e `tamanhos`
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
                   'created_at' => $produto->created_at
                    ? $produto->created_at->format('d/m/Y H:i')
                    : null,
            ];
        });

        return Inertia::render('Home', [
            'produtoSwiper' => $produtos,
        ]);
    }
}
