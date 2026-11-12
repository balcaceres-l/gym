@extends('layouts.app')

@section('titulo', 'Editar · ' . $socio->nombre_completo)

@section('contenido')
    <a href="{{ route('socios.show', $socio) }}" class="volver">‹ Volver a la ficha del socio</a>

    <div class="cabecera-pagina">
        <div>
            <h1>Editar socio</h1>
            <p class="subtitulo">{{ $socio->nombre_completo }} · DUI {{ $socio->dui }}</p>
        </div>
    </div>

    <div class="tarjeta">
        {{-- [CRUD-UPDATE] [SEGURIDAD] Directiva @csrf y @method('PUT') para actualización REST --}}
        <form action="{{ route('socios.update', $socio) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            @include('socios._form')

            <div class="formulario__acciones">
                <a href="{{ route('socios.show', $socio) }}" class="boton boton--secundario">Cancelar</a>
                <button type="submit" class="boton">Actualizar datos</button>
            </div>
        </form>
    </div>
@endsection
