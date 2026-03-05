<?php

namespace App\Http\Controllers\App;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Produto;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
         $user = Auth::user(); 

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
                'created_at' => optional($product->created_at)->format('d/m/Y H:i'),
            ];
        });

        return Inertia::render('admin/Dashboard', ['user'=>$user, 'produto'=>$products]);
    }
}
