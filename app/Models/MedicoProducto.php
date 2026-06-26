<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MedicoProducto extends Model
{
    protected $table = 'medico_productos';

    protected $fillable = [
        'tipo',
        'nombre',
        'stock_minimo',
        'fecha_caducidad',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'stock_minimo' => 'float',
            'fecha_caducidad' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(MedicoProductoAlias::class, 'producto_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MedicoKardexMovimiento::class, 'producto_id');
    }

    public function saldoActual(): float
    {
        $kardex = MedicoKardex::query()
            ->where('nombre', $this->nombre)
            ->orderBy('fecha_inicio')
            ->first();

        if (! $kardex) {
            $movIngresos = (float) $this->movimientos()->whereIn('tipo', ['ingreso', 'ajuste'])->where('cantidad', '>', 0)->sum('cantidad');
            $movSalidas = (float) $this->movimientos()->where('tipo', 'salida')->sum('cantidad');

            return $movIngresos - $movSalidas;
        }

        $base = (float) $kardex->saldo_anterior;
        $kardexIngresos = (float) MedicoKardex::query()
            ->where('nombre', $this->nombre)
            ->sum('ingresos');

        $corte = $kardex->fecha_inicio;

        $movIngresos = (float) $this->movimientos()
            ->whereIn('tipo', ['ingreso', 'ajuste'])
            ->where('cantidad', '>', 0)
            ->whereDate('fecha_movimiento', '>=', $corte)
            ->sum('cantidad');

        $movSalidas = (float) $this->movimientos()
            ->where('tipo', 'salida')
            ->whereDate('fecha_movimiento', '>=', $corte)
            ->sum('cantidad');

        $ajustesNegativos = abs((float) $this->movimientos()
            ->where('tipo', 'ajuste')
            ->where('cantidad', '<', 0)
            ->whereDate('fecha_movimiento', '>=', $corte)
            ->sum('cantidad'));

        return $base + $kardexIngresos + $movIngresos - $movSalidas - $ajustesNegativos;
    }

    public static function normalizarNombre(string $valor): string
    {
        $valor = Str::of($valor)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish();

        return (string) $valor;
    }

    public static function resolverPorNombre(?string $nombre): ?self
    {
        $nombre = trim((string) $nombre);

        if ($nombre === '') {
            return null;
        }

        $normalizado = self::normalizarNombre($nombre);

        $alias = MedicoProductoAlias::query()
            ->with('producto')
            ->where('alias_normalizado', $normalizado)
            ->first();

        if ($alias?->producto) {
            return $alias->producto;
        }

        return self::query()
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->first();
    }
}
