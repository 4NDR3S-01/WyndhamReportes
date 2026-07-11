# Documentación Técnica — Sistema de Gestión Hotelera Wyndham Manta

**Versión:** 1.0.0  
**Última actualización:** Julio 2026  
**Sistema:** Panel de Administración Médico y Cocina  
**Hotel:** Wyndham Manta

---

## Índice

1. [Resumen del proyecto](#1-resumen-del-proyecto)
2. [Arquitectura general](#2-arquitectura-general)
3. [Stack tecnológico](#3-stack-tecnológico)
4. [Estructura del proyecto](#4-estructura-del-proyecto)
5. [Base de datos](#5-base-de-datos)
6. [Módulo Cocina](#6-módulo-cocina)
7. [Módulo Médico](#7-módulo-médico)
8. [Sistema de Kardex (Inventario)](#8-sistema-de-kardex)
9. [Servicios](#9-servicios)
10. [Autenticación y permisos](#10-autenticación-y-permisos)
11. [Tema y estilos](#11-tema-y-estilos)
12. [Importación de datos](#12-importación-de-datos)
13. [Despliegue](#13-despliegue)
14. [Comandos esenciales](#14-comandos-esenciales)
15. [Migración a producción](#15-migración-a-producción)

---

## 1. Resumen del proyecto

Sistema web interno para el Hotel Wyndham Manta desarrollado con **Laravel 13.8 + Filament 5.6**. Consta de dos módulos principales:

| Módulo | Propósito |
|--------|-----------|
| **Cocina** | Gestión de consumo de alimentos, importación de datos desde Excel, análisis de productos y generación de reportes |
| **Médico** | Gestión de atenciones médicas (partes diarios), pacientes, inventario de medicinas/insumos (Kardex), catálogos clínicos y reportes |

El sistema está completamente localizado al español. La UI es responsiva y soporta modo oscuro.

---

## 2. Arquitectura general

```
┌─────────────────────────────────────────────────────────────┐
│                    Filament Panel v5.6                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐  │
│  │  Inicio   │  │  Cocina  │  │  Médico  │  │  Reportes  │  │
│  │ Dashboard │  │ Dashboard│  │ Dashboard│  │            │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘  │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Páginas Livewire full-page                │   │
│  │  Partes Diarios · Pacientes · Inventario · Catálogos  │   │
│  │  Subir Datos · Reportes                               │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Widgets Chart.js                        │   │
│  │  Atenciones Diarias · Causas · Áreas · Medicamentos  │   │
│  │  Consumo Diario · Productos                          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Laravel 13.8                              │
│  ┌─────────┐  ┌──────────┐  ┌────────┐  ┌───────────────┐  │
│  │ Models  │  │ Services │  │ Routes │  │  Seeders      │  │
│  │ Eloquent│  │  Lógica  │  │ web.php│  │  Población BD │  │
│  └─────────┘  └──────────┘  └────────┘  └───────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Base de Datos                             │
│  ┌───────┐  ┌────────────────┐  ┌────────────────────┐     │
│  │Local  │  │  MySQL (Prod)  │  │  SQLite (Dev)      │     │
│  └───────┘  └────────────────┘  └────────────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

### Flujo de datos general

1. **Cocina**: Archivos Excel → Subida → Procesamiento → `cocina_consumos` → Dashboards/Reportes
2. **Médico**: Atención médica → Parte diario → Despacho de medicamentos → Movimiento de Kardex → Reportes
3. **Inventario**: Productos → Kardex mensual → Movimientos (ingresos/egresos/ajustes) → Cierres

---

## 3. Stack tecnológico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | PHP / Laravel | ^8.3 / ^13.8 |
| Panel Admin | Filament | ^5.6 |
| Base de datos | SQLite (dev) / MySQL (prod) | — |
| Frontend | Vite + Tailwind CSS | Vite ^8 / Tailwind ^4 |
| Gráficos | Chart.js (vía Filament) | — |
| Excel | PhpSpreadsheet | ^5.8 |
| Permisos | Spatie Laravel Permission | ^8.0 |
| Auditoría | Spatie Activity Log | \* |
| Procesamiento imágenes | Intervention Image | ^4.1 |
| Tinker | Laravel Tinker | ^3.0 |

### Paquetes de desarrollo

| Paquete | Propósito |
|---------|-----------|
| Laravel Pint | Formateo de código PHP |
| Laravel Pail | Logs en tiempo real |
| PHPUnit | Pruebas unitarias |
| Collision | Mejora de errores en consola |

---

## 4. Estructura del proyecto

```
├── app/
│   ├── Filament/
│   │   ├── Pages/                  # Páginas del panel
│   │   │   ├── Inicio.php          # Dashboard principal
│   │   │   ├── Cocina.php          # Dashboard de cocina
│   │   │   ├── Medico.php          # Dashboard médico
│   │   │   ├── MedicoPartesDiarios.php  # CRUD partes diarios
│   │   │   ├── MedicoPacientes.php      # CRUD pacientes
│   │   │   ├── MedicoCatalogos.php      # CRUD catálogos base
│   │   │   ├── MedicoInventario.php     # Inventario + movimientos
│   │   │   ├── ReporteCocina.php        # Subida de archivos
│   │   │   ├── ReportesCocina.php       # Reportes cocina
│   │   │   └── ReportesMedico.php       # Reportes médico
│   │   └── Widgets/
│   │       ├── InicioChartWidget.php
│   │       ├── CocinaStatsWidget.php
│   │       ├── CocinaProductosChartWidget.php
│   │       ├── CocinaConsumoDiarioWidget.php
│   │       ├── MedicoStatsWidget.php
│   │       ├── MedicoAtencionesDiariasWidget.php
│   │       ├── MedicoCausasChartWidget.php
│   │       ├── MedicoAreasChartWidget.php
│   │       ├── MedicoMedicamentosChartWidget.php
│   │       └── MedicoKardexMensualWidget.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Area.php, Cargo.php, Causa.php, Diagnostico.php
│   │   ├── Medicamento.php, EntidadCertificado.php
│   │   ├── TipoCertificado.php, TipoSalida.php, Incidente.php
│   │   ├── MedicoPaciente.php
│   │   ├── MedicoPacienteExamen.php, MedicoPacienteVisita.php
│   │   ├── MedicoParteDiario.php
│   │   ├── MedicoParteMedicamento.php
│   │   ├── MedicoProducto.php, MedicoProductoAlias.php
│   │   ├── MedicoKardex.php, MedicoKardexMovimiento.php
│   │   ├── CocinaArchivoImportado.php
│   │   ├── CocinaConsumo.php
│   │   ├── CocinaProducto.php
│   │   └── CocinaImportacionError.php
│   ├── Services/
│   │   ├── Cocina/
│   │   │   └── ProcesadorArchivoConsumo.php
│   │   └── Medico/
│   │       ├── InventarioMedicoService.php
│   │       └── KardexMensualService.php
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php    # Configuración del panel
├── database/
│   ├── migrations/          # Migraciones de BD (~15 archivos)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── MedicoCatalogosSeeder.php
│       ├── MedicoNominaSeeder.php
│       ├── MedicoPartesDiariosSeeder.php
│       ├── MedicoProductosInventarioSeeder.php
│       └── MedicoKardexSeeder.php
├── resources/
│   ├── css/filament/admin/theme.css    # Tema personalizado
│   └── views/filament/pages/           # Vistas Blade (10 archivos)
├── config/
│   ├── app.php, auth.php, database.php
│   ├── permission.php                  # Config Spatie
│   └── filament.php                    # Config Filament (implícita)
├── assets/
│   └── DEPARTAMENTO_MEDICO.xlsx        # Datos semilla del seeder
├── public/
│   └── images/                         # Logo, portada, favicon
├── routes/
│   └── web.php                         # Redirección / → /admin
└── migrate_to_mysql.php                # Script migración prod
```

### Patrón de diseño

**No se usan Filament Resources.** Cada página es un componente Livewire full-page que extiende `Filament\Pages\Page`:

- El estado (formularios, filtros, paginación) se maneja con **propiedades públicas de PHP**.
- Las operaciones CRUD usan `Model::query()->updateOrCreate()` y **validación inline**, no Form Objects ni Resource tables.
- Las vistas son archivos Blade manuales en `resources/views/filament/pages/`.

---

## 5. Base de datos

### 5.1 Esquema general

El sistema tiene ~20 tablas distribuidas en tres grupos:

#### Tablas del sistema (Laravel + Paquetes)

| Tabla | Propósito |
|-------|-----------|
| `users` | Usuarios del sistema |
| `personal_access_tokens` | Tokens de autenticación |
| `cache`, `cache_locks` | Caché |
| `sessions` | Sesiones (base de datos) |
| `jobs`, `job_batches` | Cola de trabajos |
| `permissions`, `roles`, `model_has_roles`, etc. | Spatie Permission |

#### Tablas del módulo Cocina

| Tabla | Propósito |
|-------|-----------|
| `cocina_archivos_importados` | Registro de archivos Excel subidos |
| `cocina_consumos` | Registros de consumo diario |
| `cocina_productos` | Catálogo de productos de cocina |
| `cocina_importacion_errores` | Errores durante la importación |

#### Tablas del módulo Médico

| Tabla | Propósito |
|-------|-----------|
| `medico_partes_diarios` | Atenciones médicas (registro principal) |
| `medico_parte_medicamentos` | Medicación administrada por atención |
| `medico_pacientes` | Pacientes del departamento médico |
| `medico_paciente_examenes` | Exámenes ocupacionales por paciente |
| `medico_paciente_visitas` | Visitas anuales por paciente |
| `medico_productos` | Productos (medicinas e insumos) |
| `medico_producto_aliases` | Nombres alternativos para productos |
| `medico_kardex` | Saldos mensuales por producto |
| `medico_kardex_movimientos` | Movimientos individuales (ingreso/salida/ajuste) |
| `areas`, `cargos`, `causas`, `diagnosticos` | Catálogos clínicos |
| `medicamentos` | Catálogo de medicamentos |
| `tipo_certificados`, `entidad_certificados` | Catálogos de certificados |
| `tipo_salidas`, `incidentes` | Tipos de salida e incidentes |
| `cirugia_generals`, `flebologia_vasculares`, `atencion_medicas` | Catálogos adicionales |

### 5.2 Modelo de datos relacional (módulo Médico)

```
medico_pacientes
  ├── area_id → areas
  └── cargo_id → cargos

medico_paciente_examenes
  └── paciente_id → medico_pacientes

medico_paciente_visitas
  └── paciente_id → medico_pacientes

medico_partes_diarios
  ├── area_id → areas
  ├── cargo_id → cargos
  ├── causa_id → causas
  ├── diagnostico_id → diagnosticos
  └── entidad_certificado_id → entidad_certificados

medico_parte_medicamentos
  ├── parte_diario_id → medico_partes_diarios
  └── medicamento_id → medicamentos

medico_kardex_movimientos
  ├── kardex_id → medico_kardex
  ├── producto_id → medico_productos
  └── parte_diario_id → medico_partes_diarios
```

### 5.3 Modelo de datos (módulo Cocina)

```
cocina_archivos_importados
  └── usuario_id → users

cocina_consumos
  ├── archivo_importado_id → cocina_archivos_importados
  └── producto_id → cocina_productos

cocina_importacion_errores
  └── archivo_importado_id → cocina_archivos_importados
```

---

## 6. Módulo Cocina

### 6.1 Dashboard de Cocina (`Cocina.php`)

**Ruta:** `/admin/cocina`  
**Vista:** `cocina.blade.php`

Panel principal con:

- **Selector de fecha**: filtra el consumo del día seleccionado
- **Stat cards**: registros última fecha, productos usados, fechas registradas, total registros, productos catalogados, archivos importados
- **Consumo del día**: tabla agrupada por unidad de medida (kilo, litro, porción, gramo, unidad)
- **Top 10 productos más consumidos**: ranking histórico
- **Recomendación**: calcula cantidades sugeridas para un número objetivo de huéspedes basado en un día de referencia
- **Widgets**: 
  - `CocinaProductosChartWidget`: gráfico de barras con top 10 productos
  - `CocinaConsumoDiarioWidget`: consumo por servicio (desayuno/almuerzo/cena)

### 6.2 Subir Datos (`ReporteCocina.php`)

**Ruta:** `/admin/cocina/subir-datos`  
**Vista:** `reporte-cocina.blade.php`

- Subida de archivos Excel/CSV (máx. 10 MB)
- Almacenamiento en `storage/importaciones/cocina/`
- Previsualización de contenido antes de procesar
- Procesamiento con detección dinámica de encabezados
- Historial de archivos subidos con estado

### 6.3 Reportes de Cocina (`ReportesCocina.php`)

**Ruta:** `/admin/cocina/reportes`  
**Vista:** `reportes-cocina.blade.php`

- **Descarga por día**: Excel con productos y cantidades
- **Descarga por rango**: Excel filtrado por fechas
- **Descarga general**: Excel completo
- **Semanas consumidas**: tabla con resumen semanal
- **Errores de importación**: listado de errores

### 6.4 Modelos

| Modelo | Tabla | Propósito |
|--------|-------|-----------|
| `CocinaArchivoImportado` | `cocina_archivos_importados` | Metadatos de archivos subidos |
| `CocinaConsumo` | `cocina_consumos` | Registros de consumo (fecha, producto, cantidad) |
| `CocinaProducto` | `cocina_productos` | Catálogo de productos |
| `CocinaImportacionError` | `cocina_importacion_errores` | Errores por fila |

---

## 7. Módulo Médico

### 7.1 Dashboard de Médico (`Medico.php`)

**Ruta:** `/admin/medico`  
**Vista:** `medico.blade.php`

Panel principal con:

- **Stat cards**: total atenciones, total pacientes, total kardex, última fecha, atenciones hoy, días cubiertos
- **Selector de mes**: resumen mensual (atenciones, pacientes, días, certificados, medicamentos)
- **Selector de fecha**: resumen del día (atenciones, áreas, certificados)
- **Atenciones del día**: tabla detallada con área, cargo, causa, diagnóstico, medicación
- **Atenciones por área**: agrupación por área/departamento
- **Top 10 medicamentos**: ranking de medicamentos más usados
- **Alertas de stock**: productos con saldo ≤ 2 unidades
- **Kardex actual**: saldos de medicinas
- **Insumos**: saldos de insumos
- **Movimientos recientes**: últimos 20 movimientos del kardex
- **Widgets**:
  - `MedicoAtencionesDiariasWidget`: línea temporal mensual
  - `MedicoCausasChartWidget`: distribución de causas
  - `MedicoAreasChartWidget`: atenciones por área
  - `MedicoMedicamentosChartWidget`: top medicamentos

### 7.2 Partes Diarios (`MedicoPartesDiarios.php`)

**Ruta:** `/admin/medico/partes-diarios`  
**Vista:** `medico-partes-diarios.blade.php`

**Página principal del módulo Médico.** CRUD completo de atenciones médicas.

#### Funcionalidades

| Característica | Descripción |
|----------------|-------------|
| **Registro de atención** | Modal con formulario completo |
| **Búsqueda de pacientes** | Buscador con autocompletado (nombre, cédula, área) |
| **Quick-create paciente** | Registro rápido de nuevo paciente sin salir del modal |
| **Certificados médicos** | Tipo (REPOSO, SUBSIDIO, ALTA, etc.), entidad, horas/días, fechas, médico que certifica |
| **Medicación** | Líneas dinámicas de medicamentos con cantidad |
| **Tipos de salida** | ALTA, REPOSO, DERIVACIÓN, OBSERVACIÓN, HOSPITALIZACIÓN |
| **Incidentes** | CAIDA, CORTE, QUEMADURA, EXPOSICIÓN QUÍMICA, etc. |
| **Edición** | Carga todos los datos existentes |
| **Eliminación** | Con confirmación y reversión de movimientos de inventario |
| **Filtros** | Fecha, área, causa, tipo paciente, estado certificado, búsqueda texto |
| **Paginación** | 15 registros por página |
| **Integración inventario** | Al guardar, registra salidas de inventario automáticas |

#### Flujo de guardado

```
Guardar atención
  ├── Validar datos
  ├── Crear/actualizar MedicoParteDiario
  ├── Sincronizar medicamentos (MedicoParteMedicamento)
  ├── Registrar salidas de inventario (InventarioMedicoService)
  │   └── Para cada medicamento:
  │       └── Buscar MedicoProducto vinculado al medicamento
  │       └── Crear MedicoKardexMovimiento (tipo: salida)
  ├── Vincular paciente (existente o nuevo)
  └── Notificación de éxito
```

#### Eliminación

Al eliminar una atención se revierten los movimientos de inventario asociados.

### 7.3 Pacientes (`MedicoPacientes.php`)

**Ruta:** `/admin/medico/pacientes`  
**Vista:** `medico-pacientes.blade.php`

CRUD de pacientes con:

- **Campos**: cédula, nombres, edad, área, cargo, fecha ingreso, teléfono, tipo (colaborador, aspirante, externo, paciente, huésped)
- **Exámenes ocupacionales**: espirometría, ecografía, audiometría, optometría (con estado: pendiente/vigente/vencido)
- **Visitas anuales**: registro por año (2021-2026)
- **Historial médico**: patologías, vacunas, fichas anteriores, antecedentes, observaciones
- **Filtros**: búsqueda por nombre/cédula/área, filtro por tipo, estado
- **Detalle expandible**: información completa del paciente
- **Paginación**: 20 registros por página

### 7.4 Base Médica / Catálogos (`MedicoCatalogos.php`)

**Ruta:** `/admin/medico/base-medica`  
**Vista:** `medico-catalogos.blade.php`

Gestión unificada de 5 catálogos:

| Catálogo | Modelo | Descripción |
|----------|--------|-------------|
| Áreas | `Area` | Departamentos laborales |
| Cargos | `Cargo` | Puestos de trabajo |
| Causas | `Causa` | Motivos de atención |
| Diagnósticos | `Diagnostico` | Diagnósticos médicos (lista extensa) |
| Entidades Certificado | `EntidadCertificado` | Origen del certificado |

Todas las tablas siguen el mismo patrón: `id`, `nombre`, `activo`, timestamps.

### 7.5 Reportes Médico (`ReportesMedico.php`)

**Ruta:** `/admin/medico/reportes`  
**Vista:** `reportes-medico.blade.php`

#### Reportes descargables (Excel)

| Reporte | Descripción |
|---------|-------------|
| Parte diario por día | Atenciones de una fecha |
| Parte diario por rango | Atenciones entre fechas con todos los campos |
| Parte diario general | Todas las atenciones |
| Kardex completo | Saldos de todos los productos |
| Kardex con movimientos | Resumen + hoja de movimientos detallados |
| Kardex mensual | Un libro por mes con atenciones, causas, medicamentos, áreas |

#### Kardex mensual (generación en memoria)

- Calcula saldos desde movimientos reales (sin persistencia de snapshots)
- Muestra: producto, saldo anterior, ingresos, egresos, total, fecha caducidad
- Permite cerrar/reabrir el kardex visualmente
- Exportable a Excel

---

## 8. Sistema de Kardex

### 8.1 Arquitectura

```
MedicoProducto
    │
    ├── 1┼→ MedicoKardex (saldo mensual por producto)
    │          │
    │          └── 1┼→ MedicoKardexMovimiento (movimiento individual)
    │                    ├── tipo: ingreso | salida | ajuste
    │                    ├── origen: manual | parte_diario
    │                    └── vinculado a MedicoParteDiario (opcional)
    │
    └── medicamento_id → Medicamento (catálogo clínico)

MedicoProductoAlias
    └── producto_id → MedicoProducto (nombres alternativos para búsqueda)
```

### 8.2 Servicios

#### `InventarioMedicoService`

```php
registrarMovimientoProducto(
    MedicoProducto $producto,
    string $tipo,          // ingreso | salida | ajuste
    float $cantidad,
    string $fecha,
    ?string $responsable,
    string $origen,         // manual | parte_diario
    ?MedicoParteDiario $parte,
    ?string $observacion
): MedicoKardexMovimiento
```

- Asegura que exista un registro `MedicoKardex` para el producto
- Crea el movimiento en `medico_kardex_movimientos`

#### `KardexMensualService`

```php
generar(string $desde, string $hasta): array
```

- Calcula saldos desde movimientos reales
- No persiste snapshots (cálculo bajo demanda)
- Retorna array de items con: producto_id, tipo, nombre, saldo_anterior, ingresos, egresos, total, fecha_caducidad

```php
mesesDisponibles(): Collection
```

- Meses con movimientos para el selector de historial

### 8.3 Inventario (`MedicoInventario.php`)

**Ruta:** `/admin/medico/inventario`

- **Productos**: CRUD de medicinas e insumos con stock mínimo, fecha caducidad
- **Movimientos**: registro de ingresos, salidas y ajustes
- **Sincronización automática**: al crear un producto se sincroniza con el catálogo `Medicamento`
- **Stock bajo**: alerta visual de productos bajo mínimo
- **Detalle de producto**: saldo actual y últimos movimientos

---

## 9. Servicios

### 9.1 `ProcesadorArchivoConsumo`

**Propósito:** Procesa archivos Excel/CSV del módulo Cocina.

**Flujo:**
1. Lee el archivo con PhpSpreadsheet
2. Detecta encabezados dinámicamente (fecha, producto, cantidad)
3. Normaliza fechas (múltiples formatos)
4. Resuelve o crea productos
5. Deduplica por hash SHA-256
6. Crea registros en `cocina_consumos`
7. Reporta errores por fila

### 9.2 `InventarioMedicoService`

**Propósito:** Registro de movimientos de inventario médico.

**Características:**
- Creación automática de kardex para nuevos productos
- Vinculación con partes diarios (dispensación)
- Origen `manual` vs `parte_diario`

### 9.3 `KardexMensualService`

**Propósito:** Generación de reportes kardex bajo demanda.

**Características:**
- Cálculo en memoria desde movimientos reales
- Sin persistencia de snapshots (eliminó tablas redundantes)
- Soporte para rango de fechas

---

## 10. Autenticación y permisos

### 10.1 Usuarios

- **Admin por defecto:** `admin@gmail.com` / `admin123`
- **Rol:** `super_admin`

### 10.2 Sistema de permisos

Usa `spatie/laravel-permission`:
- Roles y permisos administrables
- Tablas: `permissions`, `roles`, `model_has_roles`, `role_has_permissions`, `model_has_permissions`

### 10.3 Personalización del login

La página de login tiene:
- Imagen de fondo (`/images/portada.png`)
- Tarjeta glassmorphism con blur
- Barra decorativa superior con gradiente (azul → celeste → amarillo → coral)
- Animación de entrada
- Adaptación responsiva y modo oscuro

### 10.4 Barra superior personalizada

- Reloj en vivo con fecha
- Botón de alternancia modo oscuro/claro
- Botón de cerrar sesión con modal de confirmación

---

## 11. Tema y estilos

### 11.1 Tema personalizado

Archivo: `resources/css/filament/admin/theme.css`

### 11.2 Paleta de colores

| Variable | Color | Uso |
|----------|-------|-----|
| `--color-medical-*` | Azul cielo | Acentos médicos |
| `--color-brand-*` | Azul marino `#0B3B60` | Marca principal |
| `--color-ocean-*` | Teal costero `#0E7490` | Enlaces, gráficos |
| `--color-coral-*` | Coral `#D9704A` | Acentos de alerta |
| `--color-sand-*` | Arena `#A68064` | Neutros cálidos |
| `--color-tide-*` | Índigo `#3B4C82` | Variantes |

### 11.3 Componentes CSS utilitarios

- `.stat`, `.stat-icon`, `.stat-value`, `.stat-label` — Tarjetas de estadísticas
- `.card`, `.card-header` — Contenedores tipo tarjeta
- `.pill-group` / `.pill` — Botones de selección tipo píldora
- `.btn-primary`, `.btn-ghost`, `.btn-danger-ghost`, `.btn-outline` — Botones
- `.scroll-thin` — Scroll personalizado
- `.page-enter` — Animación de entrada

---

## 12. Importación de datos

### 12.1 Seeders

```bash
php artisan migrate:fresh --seed
```

| Seeder | Fuente | Propósito |
|--------|--------|-----------|
| `MedicoCatalogosSeeder` | Excel embebido | Catálogos (áreas, cargos, causas, etc.) |
| `MedicoNominaSeeder` | Excel embebido | Pacientes iniciales |
| `MedicoPartesDiariosSeeder` | — | Datos de prueba de atenciones |
| `MedicoProductosInventarioSeeder` | — | Productos de inventario de prueba |
| `MedicoKardexSeeder` | — | Movimientos de kardex de prueba |

### 12.2 Importación de archivos Cocina

- Formatos soportados: `.xlsx`, `.xls`, `.csv`
- Tamaño máximo: 10 MB
- Almacenamiento: `storage/importaciones/cocina/`
- Deduplicación: hash SHA-256 por archivo+fila
- Detección dinámica de encabezados (flexible ante variaciones de nombre de columnas)

---

## 13. Despliegue

### 13.1 Requisitos del servidor

- PHP ^8.3
- Composer
- Node.js + NPM
- Base de datos: SQLite (desarrollo) o MySQL (producción)
- Extensión PHP: `gd`, `zip`, `xml`, `mbstring`

### 13.2 Instalación

```bash
# 1. Clonar repositorio
git clone <url> wyndham-reportes
cd wyndham-reportes

# 2. Configurar entorno
cp .env.example .env
# Editar .env con credenciales de BD

# 3. Instalación completa
composer setup

# 4. Poblar base de datos (opcional)
php artisan migrate:fresh --seed

# 5. Iniciar en desarrollo
composer dev
```

### 13.3 Variables de entorno clave

| Variable | Descripción | Dev | Prod |
|----------|-------------|-----|------|
| `DB_CONNECTION` | Tipo BD | `sqlite` | `mysql` |
| `DB_DATABASE` | Ruta/nombre BD | `database/database.sqlite` | `wyndham_reportes` |
| `SESSION_DRIVER` | Driver sesión | `file` | `database` |
| `QUEUE_CONNECTION` | Driver cola | `sync` | `database` |

---

## 14. Comandos esenciales

```bash
composer setup        # Instalación completa
composer dev          # Desarrollo (servidor + cola + logs + vite)
composer test         # Pruebas
php artisan migrate:fresh --seed   # Recrear BD con datos
php artisan pint      # Formatear código PHP
```

---

## 15. Migración a producción

Usar el script `migrate_to_mysql.php` para copiar datos desde SQLite local a MySQL.

**Variables de entorno requeridas:**

```
SOURCE_SQLITE_PATH=            # Ruta al SQLite de origen
TARGET_DB_HOST=                # Host MySQL
TARGET_DB_PORT=3306            # Puerto MySQL
TARGET_DB_DATABASE=            # Nombre BD producción
TARGET_DB_USERNAME=            # Usuario
TARGET_DB_PASSWORD=            # Contraseña
```

**Proceso:**
1. En producción: `php artisan migrate --force` (crear esquema)
2. En local: `php migrate_to_mysql.php` (copiar datos)

---

## Apéndice A: Migraciones

| Archivo | Tablas creadas |
|---------|---------------|
| `0001_01_01_000000_create_users_table.php` | `users`, `personal_access_tokens` |
| `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` |
| `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches` |
| `2026_06_17_011908_create_permission_tables.php` | `permissions`, `roles`, etc. |
| `2026_06_17_012008_create_activity_log_table.php` | `activity_log` |
| `2026_06_17_030000_create_cocina_*.php` | Tablas cocina |
| `2026_07_01_200000_rebuild_medical_database.php` | Tablas médicas (principal) |
| `2026_07_07_000001_add_medicamento_id_to_medico_productos.php` | Migración vinculación |
| `2026_07_07_000002_add_parte_diario_id_to_kardex_movimientos.php` | Migración kardex |
| `2026_07_07_000003_drop_kardex_cierres_tables.php` | Limpieza tablas obsoletas |
| `2026_07_08_000002_add_tipo_salida_incidente_to_partes_diarios.php` | Nuevos campos |
| `2026_07_08_000003_convert_catalogs_to_fixed_strings.php` | Migración catálogos |

## Apéndice B: Vistas Blade

| Archivo | Página |
|---------|--------|
| `inicio.blade.php` | Dashboard principal |
| `cocina.blade.php` | Dashboard Cocina |
| `medico.blade.php` | Dashboard Médico |
| `medico-partes-diarios.blade.php` | CRUD Partes Diarios |
| `medico-pacientes.blade.php` | CRUD Pacientes |
| `medico-catalogos.blade.php` | CRUD Catálogos |
| `medico-inventario.blade.php` | Inventario |
| `reporte-cocina.blade.php` | Subir datos Cocina |
| `reportes-cocina.blade.php` | Reportes Cocina |
| `reportes-medico.blade.php` | Reportes Médico |

---

> **Documentación generada en julio 2026 para el Sistema de Gestión Hotelera Wyndham Manta.**
