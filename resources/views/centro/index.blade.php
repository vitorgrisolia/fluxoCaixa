@extends('layouts.base')

@section('conteudo')
    
    <h1><i class="h3 bi bi-basket-fill"> Centros de Custo</i>
    
    </h1>
    
    <a href="{{ route('centro.create') }}" class="btn btn-dark mb-3 mt-3">
        Novo
    </a>

    <table class="table table-striped table-border table-hover">
        {{-- Cabeçalho --}}
        <thead> 
            <tr>
                <th>Ações</th>
                <th>ID</th>
                <th>Tipo</th>
                <th>Centro de Custo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($centros as $centro)
                
                <tr>
                    <td>
                        <a href="{{ route('centro.edit', ['id'=>$centro->id_centro_custo]) }}" class="btn btn-success">
                            Editar
                        </a>
                        <a href="{{ route('centro.destroy', ['id'=>$centro->id_centro_custo]) }}" class="btn btn-danger">
                            Excluir
                        </a>
                    </td>
                    <td>{{ $centro->id_centro_custo }}</td>
                    <td>{{ $centro->tipo->tipo      }}</td>
                    <td>{{ $centro->centro_custo    }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection