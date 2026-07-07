<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user para Filament
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Rol super_admin si no existe
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin->assignRole($role);

        // Poblar catálogos desde Excel
        $this->call(MedicoCatalogosSeeder::class);

        // Crear productos de inventario vinculados a cada medicamento
        $this->call(MedicoProductosInventarioSeeder::class);

        // Poblar pacientes de la nómina
        $this->call(MedicoNominaSeeder::class);

        // Importar partes diarios desde el Excel
        $this->call(MedicoPartesDiariosSeeder::class);

        // Importar datos reales del KARDEX 2026 (saldos, egresos, caducidad)
        $this->call(MedicoKardexSeeder::class);
    }
}
