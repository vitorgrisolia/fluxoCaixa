<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function cadastroRapidoNoLeitor(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'lote' => ['nullable', 'string', 'max:100'],
            'codigo_barras' => ['required', 'string', 'max:100', 'unique:produtos,codigo_barras'],
            'quantidade_inicial' => ['required', 'integer', 'min:1'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
            'tipo_quantidade' => ['required', 'in:caixa,unidade'],
            'validade' => ['required', 'date'],
            'preco_compra' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['nullable', 'numeric', 'min:0'],
            'adicionar_ao_leitor' => ['nullable', 'in:sim,nao'],
            'quantidade_leitor' => ['nullable', 'integer', 'min:1'],
            'filtro_retorno' => ['nullable', 'string', 'max:255'],
        ]);

        $loteInformado = trim((string) ($dados['lote'] ?? ''));
        $codigoBarras = trim((string) $dados['codigo_barras']);
        $precoCompra = (float) $dados['preco_compra'];
        $precoVenda = isset($dados['preco_venda']) ? (float) $dados['preco_venda'] : $precoCompra;

        $produto = Produto::create([
            'nome' => trim((string) $dados['nome']),
            'lote' => $loteInformado !== '' ? $loteInformado : $this->gerarLoteRapido($codigoBarras),
            'codigo_barras' => $codigoBarras,
            'quantidade' => (int) $dados['quantidade_inicial'],
            'estoque_minimo' => (int) $dados['estoque_minimo'],
            'tipo_quantidade' => $dados['tipo_quantidade'],
            'validade' => $dados['validade'],
            'preco_compra' => $precoCompra,
            'preco_venda' => $precoVenda,
        ]);

        $adicionarAoLeitor = ($dados['adicionar_ao_leitor'] ?? 'sim') === 'sim';
        if ($adicionarAoLeitor) {
            $selecao = collect($request->session()->get(self::LEITOR_SESSION_KEY, []))
                ->map(function ($quantidade) {
                    return (int) $quantidade;
                });

            $quantidadeLeitor = (int) ($dados['quantidade_leitor'] ?? 1);
            $quantidadeAtual = (int) $selecao->get((string) $produto->id_produto, $selecao->get($produto->id_produto, 0));
            $selecao->put($produto->id_produto, $quantidadeAtual + $quantidadeLeitor);
            $request->session()->put(self::LEITOR_SESSION_KEY, $selecao->all());
        }

        return redirect()->route('leitor.produtos', ['filtro' => $dados['filtro_retorno'] ?? $codigoBarras])
            ->with('success', "Produto {$produto->nome} cadastrado rapidamente no PDV com sucesso.");
    }

    public function importarLote(Request $request)
    {
        $dados = $request->validate([
            'arquivo_importacao' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xls,xlsx'],
            'atualizar_existentes' => ['nullable', 'in:sim,nao'],
        ]);

        try {
            $registros = $this->lerRegistrosImportacao($request->file('arquivo_importacao'));
        } catch (\RuntimeException $exception) {
            return redirect()->route('produto.index')
                ->with('danger', $exception->getMessage());
        }

        if (empty($registros)) {
            return redirect()->route('produto.index')
                ->with('danger', 'Arquivo sem registros validos para importacao.');
        }

        $atualizarExistentes = ($dados['atualizar_existentes'] ?? 'nao') === 'sim';
        $inseridos = 0;
        $atualizados = 0;
        $ignorados = 0;
        $erros = [];

        DB::transaction(function () use ($registros, $atualizarExistentes, &$inseridos, &$atualizados, &$ignorados, &$erros) {
            foreach ($registros as $indice => $registro) {
                $linhaArquivo = $indice + 2;
                $normalizado = $this->normalizarRegistroImportacao($registro);

                if (! $normalizado['ok']) {
                    $erros[] = "Linha {$linhaArquivo}: {$normalizado['erro']}";
                    continue;
                }

                $payload = $normalizado['dados'];
                $existente = null;

                if (!empty($payload['codigo_barras'])) {
                    $existente = Produto::where('codigo_barras', $payload['codigo_barras'])->first();
                }

                if (!$existente && !empty($payload['lote'])) {
                    $existente = Produto::where('lote', $payload['lote'])->first();
                }

                if ($existente) {
                    if ($atualizarExistentes) {
                        $existente->fill($payload);
                        $existente->save();
                        $atualizados++;
                    } else {
                        $ignorados++;
                    }
                    continue;
                }

                Produto::create($payload);
                $inseridos++;
            }
        });

        $mensagem = "Importacao concluida: {$inseridos} inserido(s), {$atualizados} atualizado(s), {$ignorados} ignorado(s).";

        $resposta = redirect()->route('produto.index')->with('success', $mensagem);
        if (!empty($erros)) {
            $resposta = $resposta->with('warning', implode(' | ', array_slice($erros, 0, 10)));
        }

        return $resposta;
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

    private function gerarLoteRapido(string $codigoBarras): string
    {
        $codigoSanitizado = preg_replace('/[^a-zA-Z0-9]/', '', $codigoBarras);
        if ($codigoSanitizado === null || $codigoSanitizado === '') {
            $codigoSanitizado = strtoupper(Str::random(6));
        }

        return 'RAPIDO-'.$codigoSanitizado.'-'.now()->format('YmdHis');
    }

    private function lerRegistrosImportacao(UploadedFile $arquivo): array
    {
        $extensao = strtolower((string) $arquivo->getClientOriginalExtension());

        if (in_array($extensao, ['csv', 'txt'], true)) {
            return $this->lerRegistrosCsv($arquivo->getRealPath());
        }

        if (in_array($extensao, ['xls', 'xlsx'], true)) {
            return $this->lerRegistrosSpreadsheet($arquivo->getRealPath(), $extensao);
        }

        throw new \RuntimeException('Formato de arquivo nao suportado para importacao.');
    }

    private function lerRegistrosCsv(string $caminho): array
    {
        $handle = fopen($caminho, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Nao foi possivel abrir o arquivo CSV.');
        }

        $primeiraLinha = fgets($handle);
        rewind($handle);

        $delimitador = ';';
        if ($primeiraLinha !== false) {
            $opcoes = [';', ',', "\t"];
            $maior = -1;
            foreach ($opcoes as $opcao) {
                $total = substr_count($primeiraLinha, $opcao);
                if ($total > $maior) {
                    $maior = $total;
                    $delimitador = $opcao;
                }
            }
        }

        $cabecalho = fgetcsv($handle, 0, $delimitador);
        if ($cabecalho === false) {
            fclose($handle);
            return [];
        }

        $cabecalhoMapeado = array_map(function ($item) {
            return $this->normalizarCabecalho((string) $item);
        }, $cabecalho);

        $registros = [];
        while (($linha = fgetcsv($handle, 0, $delimitador)) !== false) {
            if ($this->linhaVazia($linha)) {
                continue;
            }

            $registros[] = $this->combinarCabecalhoEValores($cabecalhoMapeado, $linha);
        }

        fclose($handle);

        return $registros;
    }

    private function lerRegistrosSpreadsheet(string $caminho, string $extensao): array
    {
        if ($extensao === 'xlsx') {
            if (! class_exists(\Shuchkin\SimpleXLSX::class)) {
                throw new \RuntimeException('Biblioteca de leitura XLSX nao encontrada.');
            }

            $xlsx = \Shuchkin\SimpleXLSX::parseFile($caminho);
            if (! $xlsx) {
                throw new \RuntimeException('Falha ao ler XLSX: '.\Shuchkin\SimpleXLSX::parseError());
            }

            $linhas = $xlsx->rows();
        } else {
            if (! class_exists(\Shuchkin\SimpleXLS::class)) {
                throw new \RuntimeException('Biblioteca de leitura XLS nao encontrada.');
            }

            $xls = \Shuchkin\SimpleXLS::parseFile($caminho);
            if (! $xls) {
                throw new \RuntimeException('Falha ao ler XLS: '.\Shuchkin\SimpleXLS::parseError());
            }

            $linhas = $xls->rows();
        }

        if (empty($linhas)) {
            return [];
        }

        $cabecalho = array_shift($linhas);
        $cabecalhoMapeado = array_map(function ($item) {
            return $this->normalizarCabecalho((string) $item);
        }, $cabecalho);

        $registros = [];
        foreach ($linhas as $linha) {
            if ($this->linhaVazia($linha)) {
                continue;
            }

            $registros[] = $this->combinarCabecalhoEValores($cabecalhoMapeado, $linha);
        }

        return $registros;
    }

    private function combinarCabecalhoEValores(array $cabecalhoMapeado, array $linha): array
    {
        $registro = [];

        foreach ($cabecalhoMapeado as $indice => $chave) {
            if ($chave === '') {
                continue;
            }

            $valor = isset($linha[$indice]) ? trim((string) $linha[$indice]) : '';
            $registro[$chave] = $valor;
        }

        return $registro;
    }

    private function linhaVazia(array $linha): bool
    {
        foreach ($linha as $coluna) {
            if (trim((string) $coluna) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizarCabecalho(string $texto): string
    {
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);
        $texto = Str::ascii($texto ?? '');
        $texto = strtolower(trim($texto));
        $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
        $texto = trim((string) $texto, '_');

        return $texto;
    }

    private function normalizarRegistroImportacao(array $registro): array
    {
        $nome = $this->valorPorChaves($registro, ['nome', 'produto', 'descricao']);
        if ($nome === '') {
            return ['ok' => false, 'erro' => 'nome/produto obrigatorio.', 'dados' => []];
        }

        $codigoBarras = $this->valorPorChaves($registro, ['codigo_barras', 'cod_barras', 'ean', 'codigo_de_barras']);
        $lote = $this->valorPorChaves($registro, ['lote', 'codigo_lote']);

        if ($codigoBarras !== '') {
            $codigoBarras = preg_replace('/\s+/', '', $codigoBarras) ?? $codigoBarras;
        }

        if ($lote === '') {
            $lote = $codigoBarras !== '' ? 'IMP-'.$codigoBarras : 'IMP-'.strtoupper(Str::random(10));
        }

        $quantidade = $this->parseInteiro($this->valorPorChaves($registro, ['quantidade', 'qtd', 'estoque']), 0);
        $estoqueMinimo = $this->parseInteiro($this->valorPorChaves($registro, ['estoque_minimo', 'estoque_min', 'minimo']), 0);
        $precoCompra = $this->parseDecimal($this->valorPorChaves($registro, ['preco_compra', 'custo', 'valor_compra']), 0);

        $precoVendaBruto = $this->valorPorChaves($registro, ['preco_venda', 'valor_venda', 'preco']);
        $precoVenda = $precoVendaBruto === '' ? $precoCompra : $this->parseDecimal($precoVendaBruto, $precoCompra);

        $tipoQuantidade = strtolower($this->valorPorChaves($registro, ['tipo_quantidade', 'tipo_unidade', 'unidade']));
        if (!in_array($tipoQuantidade, ['caixa', 'unidade'], true)) {
            $tipoQuantidade = 'unidade';
        }

        $validadeBruta = $this->valorPorChaves($registro, ['validade', 'data_validade', 'vencimento']);
        $validade = now()->addYear()->toDateString();
        if ($validadeBruta !== '') {
            try {
                $validade = Carbon::parse($validadeBruta)->toDateString();
            } catch (\Throwable $exception) {
                $validade = now()->addYear()->toDateString();
            }
        }

        return [
            'ok' => true,
            'erro' => null,
            'dados' => [
                'nome' => $nome,
                'lote' => $lote,
                'codigo_barras' => $codigoBarras !== '' ? $codigoBarras : null,
                'quantidade' => max($quantidade, 0),
                'estoque_minimo' => max($estoqueMinimo, 0),
                'tipo_quantidade' => $tipoQuantidade,
                'validade' => $validade,
                'preco_compra' => max($precoCompra, 0),
                'preco_venda' => max($precoVenda, 0),
            ],
        ];
    }

    private function valorPorChaves(array $registro, array $chaves): string
    {
        foreach ($chaves as $chave) {
            if (array_key_exists($chave, $registro)) {
                return trim((string) $registro[$chave]);
            }
        }

        return '';
    }

    private function parseInteiro(string $valor, int $padrao = 0): int
    {
        $valor = trim($valor);
        if ($valor === '') {
            return $padrao;
        }

        $numero = filter_var($valor, FILTER_VALIDATE_INT);
        if ($numero === false) {
            return $padrao;
        }

        return (int) $numero;
    }

    private function parseDecimal(string $valor, float $padrao = 0): float
    {
        $valor = trim($valor);
        if ($valor === '') {
            return $padrao;
        }

        $valor = str_replace(['R$', 'r$', ' '], '', $valor);
        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        if (!is_numeric($valor)) {
            return $padrao;
        }

        return (float) $valor;
    }
}
