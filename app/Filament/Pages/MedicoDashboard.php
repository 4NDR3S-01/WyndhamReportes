<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class MedicoDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Departamento Medico';

    protected static ?string $slug = 'medico';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.medico-dashboard';
}
