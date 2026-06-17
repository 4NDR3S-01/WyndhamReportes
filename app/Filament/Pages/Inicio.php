<?php

namespace App\Filament\Pages;

use App\Models\CocinaArchivoImportado;
use App\Models\CocinaConsumo;
use App\Models\CocinaProducto;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;

class Inicio extends Page
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Wyndham Reportes';

    protected static ?string $navigationLabel = 'Inicio';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.inicio';

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    public function getTotalArchivosProperty(): int
    {
        return CocinaArchivoImportado::query()->count();
    }

    public function getTotalConsumosProperty(): int
    {
        return CocinaConsumo::query()->count();
    }

    public function getTotalProductosProperty(): int
    {
        return CocinaProducto::query()->count();
    }
}
