{{-- Mensajes flash que envían los controladores con ->with('exito' | 'error', '...') --}}
@if (session('exito'))
    <div class="alerta alerta--exito" role="status">{{ session('exito') }}</div>
@endif

@if (session('error'))
    <div class="alerta alerta--error" role="alert">{{ session('error') }}</div>
@endif
