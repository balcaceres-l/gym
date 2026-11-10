<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entidad relacionada: pago de membresía de un socio.
 */
class Pago extends Model
{
    use HasFactory;

    public const METODOS = ['efectivo', 'tarjeta', 'transferencia'];

    // [ENCAPSULAMIENTO] Campos permitidos en asignación masiva.
    protected $fillable = [
        'socio_id',
        'plan_id',
        'fecha_pago',
        'monto',
        'metodo',
        'periodo_inicio',
        'periodo_fin',
    ];

    // [ENCAPSULAMIENTO] Tipos de datos al leer de la base.
    protected function casts(): array
    {
        return [
            'fecha_pago'     => 'date',
            'monto'          => 'decimal:2',
            'periodo_inicio' => 'date',
            'periodo_fin'    => 'date',
        ];
    }

    // [RELACION] Cada pago pertenece a un socio (N:1).
    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    // [RELACION] Cada pago corresponde a un plan (N:1).
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
