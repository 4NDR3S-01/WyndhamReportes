<?php

/*
 * Configuración de acceso por departamento (Cocina / Medico).
 *
 * Cada departamento tiene su propia contraseña, definida en el .env del servidor:
 *   COCINA_PASSWORD=...
 *   MEDICO_PASSWORD=...
 *
 * El desbloqueo es por sesión: una vez ingresada la contraseña correcta,
 * el departamento queda accesible hasta que el usuario cierre sesión.
 *
 * 'prefijos_ruta' son los prefijos de slug de las páginas de Filament que
 * pertenecen a cada departamento. Se usan para detectar a qué departamento
 * pertenece la página que se está intentando abrir.
 */

return [

    'sesion_clave' => 'departamento_desbloqueado',

    'departamentos' => [

        'cocina' => [
            'etiqueta' => 'Cocina',
            'env_password' => 'COCINA_PASSWORD',
            'password' => env('COCINA_PASSWORD'),
            'prefijos_ruta' => ['cocina'],
        ],

        'medico' => [
            'etiqueta' => 'Medico',
            'env_password' => 'MEDICO_PASSWORD',
            'password' => env('MEDICO_PASSWORD'),
            'prefijos_ruta' => ['medico'],
        ],

    ],

];
