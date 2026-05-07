<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProdutoController extends Controller
{
    private const LEITOR_SESSION_KEY = 'leitor_produtos';

    public function leitor(Request $request)
    {
        $filtro = trim((string) $request->query('filtro', ''));
        $selecaoLeitor = collect($request->session()->get(self::LEITOR_SESSION_KEY, []))
            ->map(function ($quantidade) {
                return (int) $quantidade;
            })
            ->filter(function ($quantidade) {
                return $quantidade > 0;
            });

        $produtosSelecionados = Produto::whereIn('id_produto', $selecaoLeitor->keys()->map(function ($idProduto) {
                return (int) $idProduto;
            })->all())
            ->orderBy('nome')
            ->get()
            ->map(function (Produto $produto) use ($selecaoLeitor) {
            $quantidadeSelecionada = (int) $selecaoLeitor->get((string) $produto->id_produto, $selecaoLeitor->get($produto->id_produto, 0));
            $produto->quantidade_selecionada = $quantidadeSelecionada;
            $produto->total_item_selecionado = $quantidadeSelecionada * (float) $produto->preco_venda;

            return $produto;
        })->filter(function (Produto $produto) {
            return $produto->quantidade_selecionada > 0;
        })->values();

        $totalCompra = $produtosSelecionados->sum(function (Produto $produto) {
            return $produto->total_item_selecionado;
        });

        $quantidadeItensSelecionados = $produtosSelecionados->sum(function (Produto $produto) {
            return $produto->quantidade_selecionada;
        });

        $resultadosBusca = collect();
        if ($filtro !== '') {
            $filtroNumerico = ctype_digit($filtro) ? (int) $filtro : null;
            $resultadosBusca = Produto::query()
                ->where(function ($query) use ($filtro, $filtroNumerico) {
                    if ($filtroNumerico !== null) {
                        $query->where('id_produto', $filtroNumerico)
                            ->orWhere('nome', 'like', "%{$filtro}%")
                            ->orWhere('lote', 'like', "%{$filtro}%")
                            ->orWhere('codigo_barras', 'like', "%{$filtro}%");
                        return;
                    }

                    $query->where('nome', 'like', "%{$filtro}%")
                        ->orWhere('lote', 'like', "%{$filtro}%")
                        ->orWhere('codigo_barras', 'like', "%{$filtro}%");
                })
                ->orderBy('nome')
                ->limit(30)
                ->get();
        }

        return view('produto.leitor')->with(compact(
            'filtro',
            'resultadosBusca',
            'produtosSelecionados',
            'totalCompra',
            'quantidadeItensSelecionados',
            'selecaoLeitor'
        ));
    }

    public function adicionarAoLeitor(Request $request)
    {
        $dados = $request->validate([
            'id_produto' => ['nullable', 'integer'],
            'codigo' => ['nullable', 'string', 'max:255'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'filtro_retorno' => ['nullable', 'string', 'max:255'],
        ]);

        $idProduto = isset($dados['id_produto']) ? (int) $dados['id_produto'] : null;
        $codigo = isset($dados['codigo']) ? trim((string) $dados['codigo']) : '';

        if ($idProduto === null && $codigo === '') {
            return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
                ->with('danger', 'Informe um codigo ou selecione um produto na busca.');
        }

        $produto = null;
        if ($idProduto !== null) {
            $produto = Produto::find($idProduto);
        } elseif ($codigo !== '') {
            $produto = Produto::query()
                ->where('lote', $codigo)
                ->orWhere('codigo_barras', $codigo)
                ->orWhere('id_produto', ctype_digit($codigo) ? (int) $codigo : -1)
                ->first();
        }

        if (! $produto) {
            return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? $codigo])
                ->with('danger', 'Produto nao encontrado para o codigo informado.');
        }

        $quantidadeAdicionar = (int) $dados['quantidade'];

        if ($quantidadeAdicionar > (int) $produto->quantidade) {
            return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
                ->with('danger', "Quantidade informada para {$produto->nome} maior que o estoque disponivel.");
        }

        $selecao = collect($request->session()->get(self::LEITOR_SESSION_KEY, []))
            ->map(function ($quantidade) {
                return (int) $quantidade;
            });

        $idProdutoSelecionado = (int) $produto->id_produto;
        $quantidadeAtual = (int) $selecao->get((string) $idProdutoSelecionado, $selecao->get($idProdutoSelecionado, 0));
        $novaQuantidade = $quantidadeAtual + $quantidadeAdicionar;

        if ($novaQuantidade > (int) $produto->quantidade) {
            return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
                ->with('danger', "Quantidade total para {$produto->nome} no leitor excede o estoque.");
        }

        $selecao->put($idProdutoSelecionado, $novaQuantidade);
        $request->session()->put(self::LEITOR_SESSION_KEY, $selecao->all());

        return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
            ->with('success', "Produto {$produto->nome} adicionado ao leitor.");
    }

    public function incrementarNoLeitor(Request $request, int $idProduto)
    {
        return $this->alterarQuantidadeNoLeitor($request, $idProduto, 1);
    }

    public function decrementarNoLeitor(Request $request, int $idProduto)
    {
        return $this->alterarQuantidadeNoLeitor($request, $idProduto, -1);
    }

    public function removerDoLeitor(Request $request, int $idProduto)
    {
        $dados = $request->validate([
            'filtro_retorno' => ['nullable', 'string', 'max:255'],
        ]);

        $selecao = collect($request->session()->get(self::LEITOR_SESSION_KEY, []));
        $selecao->forget((string) (int) $idProduto);
        $selecao->forget((int) $idProduto);

        if ($selecao->isEmpty()) {
            $request->session()->forget(self::LEITOR_SESSION_KEY);
        } else {
            $request->session()->put(self::LEITOR_SESSION_KEY, $selecao->all());
        }

        return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
            ->with('success', 'Produto removido do leitor.');
    }

    public function zerarLeitor(Request $request)
    {
        $request->session()->forget(self::LEITOR_SESSION_KEY);

        return redirect()->route('leitor.produtos')
            ->with('success', 'Leitor zerado com sucesso.');
    }

    private function alterarQuantidadeNoLeitor(Request $request, int $idProduto, int $delta)
    {
        $dados = $request->validate([
            'filtro_retorno' => ['nullable', 'string', 'max:255'],
        ]);

        $produto = Produto::findOrFail($idProduto);
        $selecao = collect($request->session()->get(self::LEITOR_SESSION_KEY, []))
            ->map(function ($quantidade) {
                return (int) $quantidade;
            });

        $quantidadeAtual = (int) $selecao->get((string) $idProduto, $selecao->get($idProduto, 0));
        if ($quantidadeAtual <= 0) {
            return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
                ->with('danger', 'Produto nao esta no leitor.');
        }

        $novaQuantidade = $quantidadeAtual + $delta;
        if ($novaQuantidade > (int) $produto->quantidade) {
            return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
                ->with('danger', "Nao foi possivel incrementar {$produto->nome}: estoque disponivel {$produto->quantidade}.");
        }

        if ($novaQuantidade <= 0) {
            $selecao->forget((string) $idProduto);
            $selecao->forget($idProduto);
        } else {
            $selecao->put($idProduto, $novaQuantidade);
        }

        if ($selecao->isEmpty()) {
            $request->session()->forget(self::LEITOR_SESSION_KEY);
        } else {
            $request->session()->put(self::LEITOR_SESSION_KEY, $selecao->all());
        }

        return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? null])
            ->with('success', "Quantidade de {$produto->nome} atualizada no leitor.");
    }

    public function index()
    {
        $produtos = Produto::orderBy('validade')->orderBy('nome')->get();
        $hoje = Carbon::today();
        $limiteCritico = $hoje->copy()->addDays(7);
        $limiteAtencao = $hoje->copy()->addDays(30);

        $totalVencidos = Produto::whereDate('validade', '<', $hoje)->count();
        $totalVencimentoCritico = Produto::whereBetween('validade', [$hoje, $limiteCritico])->count();
        $totalVencimentoAtencao = Produto::whereBetween('validade', [$limiteCritico->copy()->addDay(), $limiteAtencao])->count();
        $totalVencendo = $totalVencimentoCritico + $totalVencimentoAtencao;

        $totalAbaixoEstoqueMinimo = Produto::query()
            ->whereColumn('quantidade', '<=', 'estoque_minimo')
            ->count();

        $produtosReposicao = Produto::query()
            ->whereColumn('quantidade', '<=', 'estoque_minimo')
            ->orderByRaw('(estoque_minimo - quantidade) DESC')
            ->orderBy('nome')
            ->take(10)
            ->get();

        return view('produto.index')->with(compact(
            'produtos',
            'totalVencidos',
            'totalVencendo',
            'totalVencimentoCritico',
            'totalVencimentoAtencao',
            'totalAbaixoEstoqueMinimo',
            'produtosReposicao'
        ));
    }

    public function create()
    {
        $produto = null;

        return view('produto.form')->with(compact('produto'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'lote' => ['required', 'string', 'max:100'],
            'codigo_barras' => ['nullable', 'string', 'max:100', 'unique:produtos,codigo_barras'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
            'tipo_quantidade' => ['required', 'in:caixa,unidade'],
            'validade' => ['required', 'date'],
            'preco_compra' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
        ]);

        $produto = new Produto();
        $produto->fill($dados);
        $produto->save();

        return redirect()->route('produto.index')->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(int $id)
    {
        $produto = Produto::findOrFail($id);

        return view('produto.form')->with(compact('produto'));
    }

    public function update(Request $request, int $id)
    {
        $produto = Produto::findOrFail($id);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'lote' => ['required', 'string', 'max:100'],
            'codigo_barras' => ['nullable', 'string', 'max:100', 'unique:produtos,codigo_barras,'.$produto->id_produto.',id_produto'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
            'tipo_quantidade' => ['required', 'in:caixa,unidade'],
            'validade' => ['required', 'date'],
            'preco_compra' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
        ]);

        $produto->fill($dados);
        $produto->save();

        return redirect()->route('produto.index')->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        $produto = Produto::findOrFail($id);
        $produto->delete();

        return redirect()->route('produto.index')->with('success', 'Produto excluido com sucesso.');
    }
}
