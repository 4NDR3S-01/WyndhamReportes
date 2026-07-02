# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Comandos esenciales

```bash
composer setup        # Instalación completa: dependencias + migraciones + build frontend
composer dev          # Desarrollo: artisan serve + queue:listen + pail + vite dev
composer test         # php artisan config:clear && php artisan test
php artisan migrate:fresh --seed   # Recrear BD con datos de prueba
php artisan pint      # Formatear código PHP (Laravel Pint)
```

## Arquitectura general

Proyecto **Laravel 13.8 + Filament 5.6** para el hotel Wyndham Manta. Dos módulos: **Cocina** y **Medico**. Localizado completamente en español.

- **Base de datos:** SQLite en local, MySQL en producción. Queue y sesiones usan base de datos.
- **Frontend:** Vite 8 + Tailwind CSS 4. Gráficos con Chart.js (via Filament ChartWidget). ApexCharts está instalado pero no se usa.

## Estructura atípica de Filament

**Este proyecto NO usa Filament Resources.** Toda la UI son páginas Blade personalizadas que extienden `Filament\Pages\Page`. Cada página es un componente Livewire full-page: el estado (formularios, filtros, paginación) se maneja con propiedades públicas de PHP. Las operaciones CRUD usan `Model::query()->updateOrCreate()` y validación inline, no Form objects ni Resource tables.

- `app/Filament/Pages/` — Páginas (dashboards, CRUDs, reportes, importación)
- `app/Filament/Widgets/` — Widgets de gráficos (extienden `Filament\Widgets\ChartWidget`)
- `app/Providers/Filament/AdminPanelProvider.php` — Configuración del panel (grupos de navegación, tema, branding)
- **No existe** `app/Filament/Resources/`

## Navegación

La navegación del panel está en `AdminPanelProvider.php`. Los grupos colapsables son **Cocina** (Dashboard, Subir datos, Reportes) y **Medico** (Dashboard, Partes diarios, Personas, Reportes, Base médica, Medicinas y equipos, Inventario, Kardex mensual). La página de inicio (`Inicio.php`) está fuera de los grupos, con sort -1.

## Modelos y catálogos

Los modelos están en `app/Models/`. Hay 13 tablas de catálogo médico (Area, Cargo, Causa, Diagnostico, Medicamento, TipoCertificado, EntidadCertificado, TipoDescanso, TipoSalida, Incidente, CirugiaGeneral, FlebologiaVascular, AtencionMedica) que siguen el mismo patrón: `fillable: ['nombre', 'activo']`, `casts: ['activo' => 'boolean']`. Se gestionan desde `MedicoCatalogos.php` con un mapa `tipo => ModelClass`.

## Sistema de Kardex (inventario médico)

Flujo: `MedicoProducto` → `MedicoKardex` (saldo mensual por producto) → `MedicoKardexMovimiento` (ingresos/egresos/ajustes individuales) → `MedicoKardexCierre` (cierre mensual) → `MedicoKardexCierreItem` (líneas del cierre).

- `InventarioMedicoService` registra movimientos vinculados al kardex.
- `KardexMensualService` genera cierres mensuales con saldos acumulados.
- `MedicoProducto::resolverPorNombre()` busca productos por nombre normalizado o alias (fuzzy matching).

## Importación de datos

- **Cocina:** `ReporteCocina.php` sube archivos Excel a `storage/importaciones/cocina/`. `ProcesadorArchivoConsumo` procesa las filas, detecta encabezados dinámicamente, crea `CocinaProducto` nuevos, y deduplica vía `hash_unico` (SHA-256).
- **Medico:** El seeder `MedicoCatalogosSeeder` y `MedicoNominaSeeder` leen `assets/DEPARTAMENTO_MEDICO.xlsx` con múltiples hojas para poblar catálogos y pacientes.

## Tema y estilos

Tema personalizado en `resources/css/filament/admin/theme.css` que importa la base de Filament y define:
- Escala `--color-medical-*` (azul cielo 50-950)
- Clases utilitarias: `.stat`, `.stat-icon`, `.stat-value`, `.stat-label`, `.card`, `.card-header`
- Componentes: `.pill-group` / `.pill` (toggle buttons), `.btn-primary`, `.btn-ghost`, `.btn-danger-ghost`, `.btn-outline`
- Scroll personalizado `.scroll-thin`, animación `.page-enter`
- Tailwind CSS 4 con `@tailwindcss/vite`, sin archivo de configuración

## Datos de prueba

`php artisan migrate:fresh --seed` crea:
- Usuario admin: `admin@gmail.com` / `admin123` (rol `super_admin`)
- Catálogos médicos y pacientes desde el Excel en `assets/`
