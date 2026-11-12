@extends('layouts.app')

@section('titulo', 'Nuevo socio')

@section('contenido')
    <a href="{{ route('socios.index') }}" class="volver">‹ Volver al listado de socios</a>

    <div class="cabecera-pagina">
        <div>
            <h1>Registrar nuevo socio</h1>
            <p class="subtitulo">Complete los datos personales, el plan contratado y la fotografía opcional.</p>
        </div>
    </div>

    <div class="tarjeta">
        {{-- [CRUD-CREATE] [SEGURIDAD] Protección CSRF y soporte multipart para subida de imagen --}}
        <form action="{{ route('socios.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            @include('socios._form')

            <div class="formulario__acciones">
                <a href="{{ route('socios.index') }}" class="boton boton--secundario">Cancelar</a>
                <button type="submit" class="boton">Guardar socio</button>
            </div>
        </form>
    </div>
@endsection
