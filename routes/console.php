<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Medico\CargaInicialMedicoService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('medico:carga-inicial {ruta=assets/DEPARTAMENTO_MEDICO.xlsx}', function (string $ruta, CargaInicialMedicoService $service) {
    $resultado = $service->cargar(base_path($ruta));

    $this->info('Carga inicial medica completada.');
    foreach ($resultado as $clave => $valor) {
        $this->line("{$clave}: {$valor}");
    }
})->purpose('Carga los datos iniciales del modulo medico desde el Excel base');
