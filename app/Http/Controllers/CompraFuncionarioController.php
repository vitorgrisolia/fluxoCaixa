<?php

namespace App\Http\Controllers;

use App\Models\CaixaTurno;
use App\Models\Compra;
use App\Models\MovimentacaoProduto;
use App\Models\Produto;
use App\Models\VendaItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompraFuncionarioController extends Controller
{
    private const LEITOR_SESSION_KEY = 'leitor_produtos';

    public function create(Request $request)
    {
        $usuario = Auth::user();
        $turnoAberto = CaixaTurno::where('id_user', $usuario->id_user)
            ->where('status', 'aberto')
            ->orderBy('data_abertura', 'desc')
            ->first();

        if (! $turnoAberto) {
            return redirect()->route('caixa.turno.index')
                ->with('danger', 'Abra um turno de caixa antes de finalizar compras.');
        }

        $itensSelecionados = $this->obterItensSelecionados($request->session()->get(self::LEITOR_SESSION_KEY, []));

        if ($itensSelecionados->isEmpty()) {
            return redirect()->route('leitor.produtos')
                ->with('danger', 'Selecione ao menos um produto no leitor antes de finalizar.');
        }

        $totalCompra = $this->calcularTotalCompra($itensSelecionados);

        return view('compra.finalizar')->with(compact('totalCompra', 'itensSelecionados', 'turnoAberto'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'forma_pagamento' => ['required', 'in:pix,dinheiro,cartao_debito,cartao_credito,boleto,vale_alimentacao'],
            'dividir_valor' => ['nullable', 'in:sim,nao'],
            'parcelas' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);
        $validator->sometimes('dividir_valor', 'required', function ($input) {
            return $input->forma_pagamento === 'cartao_credito';
        });
        $validator->sometimes('parcelas', 'required', function ($input) {
            return $input->forma_pagamento === 'cartao_credito' && $input->dividir_valor === 'sim';
        });

        $dados = $validator->validate();
        $cartaoCredito = $dados['forma_pagamento'] === 'cartao_credito';
        $dividirValor = $cartaoCredito ? ($dados['dividir_valor'] ?? 'nao') : 'nao';
        $parcelas = ($cartaoCredito && $dividirValor === 'sim') ? (int) ($dados['parcelas'] ?? 1) : null;
        $selecaoLeitor = collect($request->session()->get(self::LEITOR_SESSION_KEY, []))
            ->map(function ($quantidade) {
                return (int) $quantidade;
            })
            ->filter(function ($quantidade) {
                return $quantidade > 0;
            });

        if ($selecaoLeitor->isEmpty()) {
            return redirect()->route('leitor.produtos')
                ->with('danger', 'Selecione ao menos um produto no leitor antes de confirmar a compra.');
        }

        $usuario = Auth::user();
        $turnoAberto = CaixaTurno::where('id_user', $usuario->id_user)
            ->where('status', 'aberto')
            ->orderBy('data_abertura', 'desc')
            ->first();

        if (! $turnoAberto) {
            return redirect()->route('caixa.turno.index')
                ->with('danger', 'Nao existe turno aberto. Abra um turno para registrar a venda.');
        }

        $totalCompra = 0;

        try {
            DB::transaction(function () use ($selecaoLeitor, $dados, $dividirValor, $parcelas, $usuario, $turnoAberto, &$totalCompra) {
                $idsProdutos = $selecaoLeitor->keys()->map(function ($idProduto) {
                    return (int) $idProduto;
                })->all();

                $produtos = Produto::whereIn('id_produto', $idsProdutos)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_produto');

                if ($produtos->count() !== count($idsProdutos)) {
                    throw new \RuntimeException('Um ou mais produtos selecionados nao estao mais disponiveis.');
                }

                foreach ($selecaoLeitor as $idProduto => $quantidadeSelecionada) {
                    $produto = $produtos->get((int) $idProduto);

                    if (! $produto) {
                        throw new \RuntimeException('Produto selecionado invalido.');
                    }

                    if ((int) $quantidadeSelecionada > (int) $produto->quantidade) {
                        throw new \RuntimeException(
                            "Estoque insuficiente para {$produto->nome}: solicitado {$quantidadeSelecionada}, disponivel {$produto->quantidade}."
                        );
                    }

                    $totalCompra += (float) $produto->preco_venda * (int) $quantidadeSelecionada;
                }

                $compra = new Compra();
                $compra->fill([
                    'data_compra' => now(),
                    'valor_total' => $totalCompra,
                    'forma_pagamento' => $dados['forma_pagamento'],
                    'dividir_valor' => $dividirValor,
                    'parcelas' => $parcelas,
                    'id_turno' => $turnoAberto->id_turno,
                ]);
                $compra->id_user = $usuario->id_user;
                $compra->save();

                foreach ($selecaoLeitor as $idProduto => $quantidadeSelecionada) {
                    $produto = $produtos->get((int) $idProduto);
                    $quantidade = (int) $quantidadeSelecionada;
                    $valorUnitarioVenda = (float) $produto->preco_venda;
                    $valorUnitarioCusto = (float) $produto->preco_compra;
                    $subtotal = $valorUnitarioVenda * $quantidade;
                    $subtotalCusto = $valorUnitarioCusto * $quantidade;

                    VendaItem::create([
                        'id_compra' => $compra->id_compra,
                        'id_produto' => $produto->id_produto,
                        'nome_produto' => $produto->nome,
                        'lote' => $produto->lote,
                        'codigo_barras' => $produto->codigo_barras,
                        'quantidade' => $quantidade,
                        'valor_unitario_venda' => $valorUnitarioVenda,
                        'valor_unitario_custo' => $valorUnitarioCusto,
                        'subtotal' => $subtotal,
                        'subtotal_custo' => $subtotalCusto,
                    ]);

                    MovimentacaoProduto::create([
                        'id_produto' => $produto->id_produto,
                        'tipo_movimentacao' => 'saida',
                        'motivo_ajuste' => 'venda_pdv',
                        'quantidade' => $quantidade,
                        'valor_unitario_venda' => $produto->preco_venda,
                        'data_movimentacao' => now()->toDateString(),
                        'observacao' => "Saida automatica da compra #{$compra->id_compra} (funcionario {$usuario->id_user}).",
                    ]);

                    $produto->quantidade = (int) $produto->quantidade - $quantidade;
                    $produto->save();
                }
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('leitor.produtos')
                ->with('danger', $exception->getMessage());
        }

        $request->session()->forget(self::LEITOR_SESSION_KEY);

        $formas = [
            'pix' => 'PIX',
            'dinheiro' => 'Dinheiro',
            'cartao_debito' => 'Cartao de debito',
            'cartao_credito' => 'Cartao de credito',
            'boleto' => 'Boleto',
            'vale_alimentacao' => 'Vale alimentacao',
        ];

        $formaSelecionada = $formas[$dados['forma_pagamento']] ?? $dados['forma_pagamento'];
        $mensagem = "Compra finalizada com sucesso. Forma de pagamento: {$formaSelecionada}.";

        if ($dividirValor === 'sim' && !empty($parcelas)) {
            $valorParcela = $totalCompra / $parcelas;

            $mensagem .= " Pagamento dividido em {$parcelas}x de R$ ".number_format($valorParcela, 2, ',', '.').".";
        }

        $mensagem .= " Venda vinculada ao turno #{$turnoAberto->id_turno}. Leitor de produtos zerado e estoque atualizado.";

        return redirect()->route('leitor.produtos')->with('success', $mensagem);
    }

    private function obterItensSelecionados(array $selecaoLeitor): Collection
    {
        $selecao = collect($selecaoLeitor)
            ->map(function ($quantidade) {
                return (int) $quantidade;
            })
            ->filter(function ($quantidade) {
                return $quantidade > 0;
            });

        if ($selecao->isEmpty()) {
            return new Collection();
        }

        $produtos = Produto::whereIn('id_produto', $selecao->keys()->all())
            ->orderBy('nome')
            ->get();

        return $produtos->map(function (Produto $produto) use ($selecao) {
            $quantidadeSelecionada = (int) $selecao->get((string) $produto->id_produto, $selecao->get($produto->id_produto, 0));
            $produto->quantidade_selecionada = $quantidadeSelecionada;
            $produto->total_item_selecionado = $quantidadeSelecionada * (float) $produto->preco_venda;

            return $produto;
        })->filter(function (Produto $produto) {
            return $produto->quantidade_selecionada > 0;
        })->values();
    }

    private function calcularTotalCompra(Collection $itensSelecionados): float
    {
        return (float) $itensSelecionados->sum(function (Produto $produto) {
            return $produto->total_item_selecionado;
        });
    }
}
