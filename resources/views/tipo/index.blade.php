@extends('layouts.base')

@section('conteudo')
    
    <h1 class="h3 bi bi-box-arrow-in-down">Tipos de Lançamentos</h1>
    <hr>
    <a href="{{ route('tipo.create') }}" class="btn btn-dark mb-3 mt-3">
        Novo
    </a>

    <table class="table table-striped table-border table-hover">
        {{-- Cabeçalho --}}
        <thead> 
            <tr>
                <th>Ações</th>
                <th>ID</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tipos->get() as $tipo)
                
                <tr>
                    <td>
                        <a href="{{ route('tipo.edit', ['id'=>$tipo->id_tipo]) }}" class="btn btn-success">
                            Editar
                        </a>
                    </td>
                    <td>{{ $tipo->id_tipo }}</td>
                    <td>{{ $tipo->tipo }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection