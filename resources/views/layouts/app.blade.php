<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Inicio') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    <header class="encabezado">
        <div class="contenedor encabezado__fila">
            <a href="{{ route('panel') }}" class="marca">
                <span class="marca__icono" aria-hidden="true">◆</span>
                {{ config('app.name') }}
            </a>

            <nav class="menu" aria-label="Menú principal">
                <a href="{{ route('panel') }}" @class(['menu__enlace', 'activo' => request()->routeIs('panel')])>Panel</a>
                <a href="{{ route('socios.index') }}" @class(['menu__enlace', 'activo' => request()->routeIs('socios.*')])>Socios</a>
                <a href="{{ route('planes.index') }}" @class(['menu__enlace', 'activo' => request()->routeIs('planes.*')])>Planes</a>
            </nav>
        </div>
    </header>

    <main class="contenedor principal">
        @include('partials.alertas')

        @yield('contenido')
    </main>

    <footer class="pie">
        <div class="contenedor">
            {{ config('app.name') }} · Caso 6: Gimnasio y membresías ·
            Desarrollo Web con Software Libre · UNICAES {{ now()->year }}
        </div>
    </footer>
</body>
</html>
