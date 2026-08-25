<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\ConfiguracaoSistema;
use App\Models\DocumentoFiscal;
use App\Models\SequenciaFiscal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentoFiscalController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentoFiscal::with(['compra.cliente'])->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $documentos = $query->paginate(25)->withQueryString();
        return view('documentoFiscal.index', compact('documentos'));
    }

    public function show(int $id)
    {
        $documento = DocumentoFiscal::with(['compra.cliente', 'compra.itens', 'eventos'])->findOrFail($id);
        return view('documentoFiscal.show', compact('documento'));
    }

    public function solicitar(Request $request, int $idCompra)
    {
        $data = $request->validate(['modelo' => ['required', 'in:55,65']]);
        $config = ConfiguracaoSistema::first();
        if (! $config || ! $config->cnpj || ! $config->regime_tributario || ! $config->uf) {
            return back()->with('danger', 'Preencha CNPJ, regime tributario e UF nas configuracoes antes de solicitar a nota.');
        }

        $documento = DB::transaction(function () use ($idCompra, $data, $config) {
            $compra = Compra::with('itens')->lockForUpdate()->findOrFail($idCompra);
            abort_if($compra->status !== 'concluida', 422, 'Somente vendas concluidas podem gerar documento fiscal.');
            abort_if($compra->itens->isEmpty(), 422, 'A venda nao possui itens fiscais preservados.');

            $existente = DocumentoFiscal::where('id_compra', $compra->id_compra)
                ->where('modelo', $data['modelo'])->where('ambiente', $config->ambiente_fiscal)->first();
            if ($existente) {
                return $existente;
            }

            $serie = (int) ($data['modelo'] === '65' ? $config->serie_nfce : $config->serie_nfe);
            $sequencia = SequenciaFiscal::where(compact('serie'))->where('modelo', $data['modelo'])
                ->where('ambiente', $config->ambiente_fiscal)->lockForUpdate()->first();
            if (! $sequencia) {
                $inicial = (int) ($data['modelo'] === '65' ? $config->proximo_numero_nfce : $config->proximo_numero_nfe);
                $sequencia = SequenciaFiscal::create(['modelo' => $data['modelo'], 'serie' => $serie, 'ambiente' => $config->ambiente_fiscal, 'proximo_numero' => $inicial]);
            }
            $numero = $sequencia->proximo_numero;
            $sequencia->increment('proximo_numero');

            $documento = DocumentoFiscal::create([
                'id_compra' => $compra->id_compra, 'idempotencia' => (string) Str::uuid(),
                'solicitado_por' => Auth::id(), 'modelo' => $data['modelo'], 'serie' => $serie,
                'numero' => $numero, 'ambiente' => $config->ambiente_fiscal,
                'status' => 'aguardando_integracao', 'solicitado_em' => now(), 'proxima_tentativa_em' => now(),
                'motivo_status' => 'Aguardando configuracao de um provedor fiscal homologado.',
            ]);
            $documento->eventos()->create(['tipo' => 'solicitacao', 'status' => 'registrado', 'motivo' => 'Solicitacao fiscal criada com numeracao reservada.']);
            return $documento;
        });

        return redirect()->route('documentos-fiscais.show', $documento->id_documento_fiscal)->with('success', 'Solicitacao registrada sem duplicidade. A transmissao aguarda o provedor fiscal.');
    }
}
