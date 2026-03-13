<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\{Lancamento, CentroCusto, User, Tipo};
use DateTime;

use Illuminate\Support\Facades\Mail;
use App\Mail\OlaLeblanc;
use App\Mail\Teste;
use Illuminate\Support\Facades\Storage;

class LancamentoController extends Controller
{
    /**
     * Mostra todos os lançamentos do Usuario
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $pesquisar = $request->pesquisar;
        $dt_inicio = null;
        $dt_fim = null;
        if ( $request->dt_inicio ||  $request->dt_fim){
            // data de inicio
            if ($request->dt_inicio) {
                $dt_inicio = $request->dt_inicio;
            } else {
                $dt = new Carbon($request->dt_fim);
                $dt->subDays(10);
                $dt_inicio = $dt;
            }
            // data de fim
            if ($request->dt_fim){
                $dt_fim = $request->dt_fim;
            } else {
                $dt = new Carbon($request->dt_inicio);
                $dt->addDays(10);
                $dt_fim = $dt;
            }           
        }

        $lancamentos = Lancamento::where( function( $query ) use ($pesquisar,$dt_inicio,$dt_fim){
                    $query->where('id_user',Auth::user()->id_user);
                    
                    if($pesquisar){
                        $query->where('descricao','like',"%{$pesquisar}%");
                    }

                    if($dt_inicio || $dt_fim){
                        $query->whereBetween('dt_faturamento', [$dt_inicio, $dt_fim]);
                    }
        })->with(['centroCusto.tipo'])
            ->orderBy('dt_faturamento', 'desc')
            ->paginate(4); 
        
        return view('lancamento.index')
                    ->with(compact('lancamentos'));
    }

    /**
     * Caminho para o form de cadastro
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $lancamento = null;    
        $centrosDeCusto = CentroCusto::orderBy('centro_custo');
        $entradas = CentroCusto::where('id_tipo',2)->orderBy('centro_custo');
        $saidas = CentroCusto::where('id_tipo',1)->orderBy('centro_custo');

        return view('lancamento.form')->with(compact('entradas','saidas','lancamento'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lancamento = new Lancamento();
        $lancamento->fill($request->all());
        $lancamento->id_user = auth::user()->id_user;    
        //subir o arquivo
        if($request->arquivo){
            $extension = $request->arquivo->getClientOriginalExtension();
            $lancamento->arquivo = $request->arquivo->storeAs('arquivos',date('YmdHis').'.'.$extension);
        }

        // $extension = $request->arquivo->getClientOriginalExtension();
        // $path = $request->arquivo->storeAs('arquivos',date('YmdHis').'.'.$extension);
        // echo $path;
        // dd($request->arquivo);
        

        $lancamento->save();

        return redirect()->route('lancamento.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lancamento  $lancamento
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $lancamento = Lancamento::find($id);
        return view('lancamento.show')
            ->with(compact('lancamento'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Lancamento  $lancamento
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $lancamento = Lancamento::find($id);
        $entradas = CentroCusto::where('id_tipo',2)->orderBy('centro_custo');
        $saidas = CentroCusto::where('id_tipo',1)->orderBy('centro_custo');
        
        return view('lancamento.form')
            ->with(compact('lancamento','entradas','saidas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lancamento  $lancamento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $lancamento = lancamento::find($id);
        //se já existe
        //e se já existe, apagar o anterior
        if($request->arquivo && $lancamento->arquivo !=''){
            if(Storage::exists($lancamento->arquivo)){
               Storage::delete($lancamento->arquivo);
            }
        }

        $lancamento->fill($request->all());

        //subir o arquivo
        if($request->arquivo){
        $extension = $request->arquivo->getClientOriginalExtension();
        $lancamento->arquivo = $request->arquivo->storeAs('arquivos',date('YmdHis').'.'.$extension);
        }

        $lancamento->save();

        return redirect()->route('lancamento.index')->with('success','Atualizado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lancamento  $lancamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $lancamento = Lancamento::find($id);
        $lancamento->delete();

        return redirect()->back();
    }
}
