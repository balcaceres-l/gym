<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Entidad principal: socio del gimnasio.
 */
class Socio extends Model
{
    use HasFactory;

    // [ENCAPSULAMIENTO] Lista blanca de campos para asignación masiva.
    // id, created_at y updated_at quedan protegidos.
    protected $fillable = [
        'plan_id',
        'dui',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'telefono',
        'correo',
        'fecha_inscripcion',
        'fecha_vencimiento',
        'imagen',
    ];

    // [ENCAPSULAMIENTO] Las fechas se convierten a objetos Carbon.
    protected function casts(): array
    {
        return [
            'fecha_nacimiento'  => 'date',
            'fecha_inscripcion' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    // [RELACION] Cada socio pertenece a un plan (N:1).
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // [RELACION] Un socio tiene muchos pagos (1:N).
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    // [ENCAPSULAMIENTO] Atributo calculado: nombre completo.
    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => "{$this->nombres} {$this->apellidos}");
    }

    // [ENCAPSULAMIENTO] Atributo calculado: edad en años a partir de la fecha de nacimiento.
    protected function edad(): Attribute
    {
        return Attribute::get(fn () => $this->fecha_nacimiento?->age);
    }

    // [REGLA-NEGOCIO] [ENCAPSULAMIENTO] La membresía está activa mientras la fecha de vencimiento no haya pasado.
    public function estaActiva(): bool
    {
        return $this->fecha_vencimiento !== null
            && $this->fecha_vencimiento->endOfDay()->isFuture();
    }

    // [REGLA-NEGOCIO] Determina si la membresía está activa y vence en los próximos 7 días.
    public function estaPorVencer(int $dias = 7): bool
    {
        if (! $this->estaActiva()) {
            return false;
        }

        return $this->fecha_vencimiento->startOfDay()->lte(today()->addDays($dias));
    }

    // [ENCAPSULAMIENTO] Días restantes hasta el vencimiento (negativo si ya venció).
    protected function diasRestantes(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->fecha_vencimiento) {
                return 0;
            }

            return (int) today()->diffInDays($this->fecha_vencimiento->startOfDay(), false);
        });
    }

    // [REGLA-NEGOCIO] [ENCAPSULAMIENTO] Texto del estado para mostrar en las vistas.
    protected function estadoMembresia(): Attribute
    {
        return Attribute::get(fn () => $this->estaActiva() ? 'Activa' : 'Vencida');
    }

    // [ENCAPSULAMIENTO] URL de la foto, o la imagen por defecto si no tiene o el archivo no existe.
    protected function imagenUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->imagen && Storage::disk('public')->exists($this->imagen)) {
                return Storage::disk('public')->url($this->imagen);
            }

            return asset('img/socio-default.svg');
        });
    }

    // Scopes reutilizables para el panel y los filtros.
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereDate('fecha_vencimiento', '>=', today());
    }

    public function scopeVencidos(Builder $query): Builder
    {
        return $query->whereDate('fecha_vencimiento', '<', today());
    }

    // [REGLA-NEGOCIO] Membresías que vencen en los próximos N días (por defecto 7 días).
    public function scopePorVencer(Builder $query, int $dias = 7): Builder
    {
        return $query
            ->whereDate('fecha_vencimiento', '>=', today())
            ->whereDate('fecha_vencimiento', '<=', today()->addDays($dias));
    }

    // Filtro de búsqueda por nombres, apellidos, DUI o correo electrónico.
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        $termino = trim((string) $termino);

        if ($termino === '') {
            return $query;
        }

        return $query->where(function (Builder $subconsulta) use ($termino) {
            $patron = "%{$termino}%";
            $subconsulta->where('nombres', 'like', $patron)
                ->orWhere('apellidos', 'like', $patron)
                ->orWhere('dui', 'like', $patron)
                ->orWhere('correo', 'like', $patron);
        });
    }
}
