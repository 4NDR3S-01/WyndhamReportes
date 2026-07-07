<?php

namespace Database\Seeders;

use App\Models\Medicamento;
use App\Models\MedicoKardex;
use App\Models\MedicoProducto;
use App\Models\MedicoProductoAlias;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicoProductosInventarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('=== Vinculando medicamentos → inventario ===');

        $medicamentos = Medicamento::query()->where('activo', true)->get();
        $creados = 0;
        $vinculados = 0;
        $conAlias = 0;

        foreach ($medicamentos as $med) {
            // Buscar si ya existe un producto con ese nombre o similar
            $producto = MedicoProducto::resolverPorNombre($med->nombre);

            if (! $producto) {
                // Crear producto de inventario nuevo
                $producto = MedicoProducto::query()->create([
                    'medicamento_id' => $med->id,
                    'tipo'           => $this->inferirTipo($med->nombre),
                    'nombre'         => $med->nombre,
                    'stock_minimo'   => 5,
                    'activo'         => true,
                ]);
                $creados++;

                // Crear registro kardex inicial
                MedicoKardex::query()->create([
                    'fecha_inicio'   => now()->startOfMonth()->toDateString(),
                    'fecha_fin'      => now()->endOfMonth()->toDateString(),
                    'tipo'           => $producto->tipo,
                    'nombre'         => $producto->nombre,
                    'saldo_anterior' => 0,
                    'ingresos'       => 0,
                    'egresos'        => 0,
                    'total'          => 0,
                    'fecha_caducidad' => $producto->fecha_caducidad?->format('Y-m-d'),
                    'hash_unico'     => hash('sha256', implode('|', ['producto', $producto->id, now()->timestamp, $med->id])),
                ]);
            } elseif (! $producto->medicamento_id) {
                // Producto existe pero no está vinculado → vincular
                $producto->update(['medicamento_id' => $med->id]);
                $vinculados++;
            }

            // Crear alias normalizado para búsqueda rápida
            $aliasNormalizado = MedicoProducto::normalizarNombre($med->nombre);
            $existeAlias = MedicoProductoAlias::query()
                ->where('producto_id', $producto->id)
                ->where('alias_normalizado', $aliasNormalizado)
                ->exists();

            if (! $existeAlias) {
                MedicoProductoAlias::query()->firstOrCreate(
                    ['alias_normalizado' => $aliasNormalizado],
                    [
                        'producto_id' => $producto->id,
                        'alias'       => $med->nombre,
                    ]
                );
                $conAlias++;
            }
        }

        $totalProductos = MedicoProducto::query()->count();
        $conVinculo = MedicoProducto::query()->whereNotNull('medicamento_id')->count();

        if ($this->command) {
            $this->command->info("  Productos creados: {$creados}");
            $this->command->info("  Productos vinculados (ya existían): {$vinculados}");
            $this->command->info("  Aliases generados: {$conAlias}");
            $this->command->info("  Total productos en inventario: {$totalProductos}");
            $this->command->info("  Productos con vínculo a medicamento: {$conVinculo} / {$totalProductos}");
        }
    }

    /**
     * Infiere el tipo de producto por palabras clave en el nombre.
     */
    private function inferirTipo(string $nombre): string
    {
        $nombre = mb_strtoupper($nombre);

        $pistas = [
            'medicina' => ['TABLETA', 'CAPSULA', 'COMPRIMIDO', 'JARABE', 'INYECTABLE',
                           'AMPOLLA', 'GOTAS', 'CREMA', 'POMADA', 'SOLUCION', 'SUSPENSION',
                           'GRAGEA', 'PASTILLA', 'OVULO', 'SUPOSITORIO', 'SPRAY', 'GEL',
                           'EMULSION', 'COLIRIO', 'PARCHE', 'SHAMPOO', 'JALEA', 'ELIXIR', 'MG'],
            'insumo'   => ['GUANTE', 'JERINGA', 'VENDA', 'GASA', 'MASCARILLA', 'TERMOMETRO',
                           'TENSIOMETRO', 'ESTETOSCOPIO', 'CATETER', 'SUERO', 'AGUJA',
                           'BAJALENGUA', 'TORNIQUETE', 'CURITA', 'ESPARADRAPO', 'ALGODON',
                           'BATA', 'GORRO', 'CUBREBOCA', 'OXIMETRO', 'GLUCOMETRO',
                           'ALCOHOL', 'AGUA OXIGENADA', 'YODO', 'CLORHEXIDINA', 'DESINFECTANTE',
                           'JABON', 'DETERGENTE', 'BOLSA', 'CONTENEDOR', 'PAPEL', 'TOALLA'],
        ];

        foreach ($pistas as $tipo => $palabras) {
            foreach ($palabras as $palabra) {
                if (str_contains($nombre, $palabra)) {
                    return $tipo;
                }
            }
        }

        return 'medicina'; // default
    }
}
