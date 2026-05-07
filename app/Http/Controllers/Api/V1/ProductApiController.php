<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query()->orderBy('nome');

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('nome', 'like', "%{$q}%")
                    ->orWhere('lote', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%");
            });
        }

        if ($request->boolean('estoque_baixo')) {
            $query->whereColumn('quantidade', '<=', 'estoque_minimo');
        }

        if ($request->filled('validade_ate')) {
            $query->whereDate('validade', '<=', $request->query('validade_ate'));
        }

        $perPage = max(1, min((int) $request->query('per_page', 25), 100));

        return ProductResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function show(int $id): ProductResource
    {
        $produto = Produto::findOrFail($id);

        return new ProductResource($produto);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'lote' => ['required', 'string', 'max:100', 'unique:produtos,lote'],
            'codigo_barras' => ['nullable', 'string', 'max:100', 'unique:produtos,codigo_barras'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
            'tipo_quantidade' => ['required', 'in:caixa,unidade'],
            'validade' => ['required', 'date'],
            'preco_compra' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
        ]);

        $produto = Produto::create($dados);

        return (new ProductResource($produto))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, int $id): ProductResource
    {
        $produto = Produto::findOrFail($id);

        $dados = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'max:255'],
            'lote' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('produtos', 'lote')->ignore($produto->id_produto, 'id_produto'),
            ],
            'codigo_barras' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('produtos', 'codigo_barras')->ignore($produto->id_produto, 'id_produto'),
            ],
            'quantidade' => ['sometimes', 'required', 'integer', 'min:0'],
            'estoque_minimo' => ['sometimes', 'required', 'integer', 'min:0'],
            'tipo_quantidade' => ['sometimes', 'required', 'in:caixa,unidade'],
            'validade' => ['sometimes', 'required', 'date'],
            'preco_compra' => ['sometimes', 'required', 'numeric', 'min:0'],
            'preco_venda' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);

        $produto->fill($dados);
        $produto->save();

        return new ProductResource($produto);
    }
}
