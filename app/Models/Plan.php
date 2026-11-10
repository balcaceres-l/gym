<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de planes de membresía.
 */
class Plan extends Model
{
    use HasFactory;

    // El plural en español no sigue la regla inglesa (plans), por eso se indica la tabla.
    protected $table = 'planes';

    // [ENCAPSULAMIENTO] Solo estos campos aceptan asignación masiva.
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracion_dias',
    ];

    // [ENCAPSULAMIENTO] Conversión automática de tipos al leer/escribir.
    protected function casts(): array
    {
        return [
            'precio'        => 'decimal:2',
            'duracion_dias' => 'integer',
        ];
    }

    // [RELACION] Un plan tiene muchos socios (1:N).
    public function socios(): HasMany
    {
        return $this->hasMany(Socio::class);
    }

    // [RELACION] Un plan aparece en muchos pagos (1:N).
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }
}
