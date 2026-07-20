<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class PuertaDepartamento extends Page
{
    protected static ?string $title = 'Acceso al departamento';

    protected static ?string $navigationLabel = 'Acceso al departamento';

    protected ?string $heading = '';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = -99;

    protected string $view = 'filament.pages.puerta-departamento';

    public ?string $depto = null;

    public ?string $password = null;

    public function mount(): void
    {
        $this->depto = request()->query('depto');

        $departamentos = config('departamentos.departamentos', []);

        if (! array_key_exists($this->depto, $departamentos)) {
            abort(404);
        }

        // Si ya está desbloqueado, ir directo al destino guardado (o al dashboard).
        $desbloqueados = Session::get(config('departamentos.sesion_clave'), []);
        if (in_array($this->depto, $desbloqueados, true)) {
            $this->irAlDestino();
        }
    }

    public function getEtiquetaDepartamento(): string
    {
        return config("departamentos.departamentos.{$this->depto}.etiqueta", 'Departamento');
    }

    /**
     * Cierra la puerta y vuelve al Inicio (no hay página anterior del
     * departamento a la que volver, ya que el acceso está bloqueado).
     */
    public function cerrar(): void
    {
        Session::forget('departamento_destino');

        $this->redirect(route('filament.admin.pages.inicio'));
    }

    public function desbloquear(): void
    {
        $passwordEsperada = config(
            "departamentos.departamentos.{$this->depto}.password"
        );

        if ($passwordEsperada === null || $passwordEsperada === '' || $this->password !== $passwordEsperada) {
            Notification::make()
                ->title('Contraseña incorrecta')
                ->danger()
                ->send();

            $this->password = '';

            return;
        }

        $clave = config('departamentos.sesion_clave');
        $desbloqueados = Session::get($clave, []);
        $desbloqueados[] = $this->depto;
        Session::put($clave, array_values(array_unique($desbloqueados)));

        Notification::make()
            ->title('Acceso concedido a ' . $this->getEtiquetaDepartamento())
            ->success()
            ->send();

        $this->irAlDestino();
    }

    protected function irAlDestino(): void
    {
        $destino = Session::pull('departamento_destino');
        Session::forget('departamento_destino');

        if ($destino) {
            $this->redirect($destino);
        } else {
            $this->redirect(route('filament.admin.pages.' . $this->depto));
        }
    }
}
