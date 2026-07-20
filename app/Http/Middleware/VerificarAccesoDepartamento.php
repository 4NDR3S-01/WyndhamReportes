<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

/**
 * Capa de seguridad por departamento.
 *
 * Filament ya autentica al usuario (un solo usuario compartido), pero este
 * middleware obliga a ingresar la contraseña del departamento antes de poder
 * ver cualquiera de sus páginas. El departamento queda "desbloqueado" en la
 * sesión hasta que el usuario cierre sesión.
 */
class VerificarAccesoDepartamento
{
    /**
     * Devuelve la clave de departamento ('cocina' | 'medico') correspondiente
     * a la ruta del panel, o null si la página no pertenece a ningún departamento.
     */
    protected function departamentoDeRuta(Request $request): ?string
    {
        // Las rutas de página del panel admin son /admin/{slug}
        $path = ltrim($request->path(), '/');           // admin/cocina/subir-datos
        $segmentos = explode('/', $path);
        $slug = $segmentos[1] ?? null;                  // cocina | medico | ...

        if ($slug === null) {
            return null;
        }

        foreach (config('departamentos.departamentos', []) as $clave => $config) {
            foreach ($config['prefijos_ruta'] as $prefijo) {
                if (str_starts_with($slug, $prefijo)) {
                    return $clave;
                }
            }
        }

        return null;
    }

    public function handle(Request $request, Closure $next)
    {
        $departamento = $this->departamentoDeRuta($request);

        // Páginas sin departamento (Inicio, login, etc.) pasan libremente.
        if ($departamento === null) {
            return $next($request);
        }

        $desbloqueados = Session::get(config('departamentos.sesion_clave'), []);

        if (in_array($departamento, $desbloqueados, true)) {
            return $next($request);
        }

        // No desbloqueado: guardamos a dónde quería ir y lo enviamos a la puerta.
        Session::put('departamento_destino', $request->fullUrl());

        return Redirect::to(
            route('filament.admin.pages.puerta-departamento', ['depto' => $departamento])
        );
    }
}
