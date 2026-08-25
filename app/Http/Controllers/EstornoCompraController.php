<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\MovimentacaoProduto;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstornoCompraController extends Controller
{
    public function store(Request $request, int $id)
    {
        $data = $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:500']]);

        DB::transaction(function () use ($id, $data) {
            $compra = Compra::with(['itens', 'documentosFiscais'])->lockForUpdate()->findOrFail($id);
            if ($compra->status !== 'concluida') {
                abort(422, 'Esta venda nao pode mais ser estornada.');
            }
            if ($compra->documentosFiscais->contains('status', 'autorizada')) {
                abort(422, 'Cancele o documento fiscal autorizado antes de estornar a venda.');
            }

            foreach ($compra->itens as $item) {
                if (! $item->id_produto) {
                    continue;
                }
                $produto = Produto::withTrashed()->lockForUpdate()->find($item->id_produto);
                if (! $produto) {
                    continue;
                }
                $produto->increment('quantidade', (int) $item->quantidade);
                MovimentacaoProduto::create([
                    'id_produto' => $produto->id_produto,
                    'tipo_movimentacao' => 'entrada',
                    'quantidade' => (int) $item->quantidade,
                    'valor_unitario_venda' => $item->valor_unitario,
                    'data_movimentacao' => now()->toDateString(),
                    'observacao' => "Estorno da compra #{$compra->id_compra}: {$data['motivo']}",
                ]);
            }

            $compra->update([
                'status' => 'estornada',
                'cancelada_em' => now(),
                'motivo_cancelamento' => $data['motivo'],
            ]);
        });

        return back()->with('success', 'Venda estornada e estoque devolvido com sucesso.');
    }
}
