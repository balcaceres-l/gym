{{-- Insignia de estado de membresía. Uso: @include('partials.estado', ['socio' => $socio]) --}}
<span @class(['insignia', 'insignia--activa' => $socio->estaActiva(), 'insignia--vencida' => ! $socio->estaActiva()])>
    {{ $socio->estado_membresia }}
</span>
