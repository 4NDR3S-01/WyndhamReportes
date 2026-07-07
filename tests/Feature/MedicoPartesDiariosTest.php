<?php

use App\Models\Area;
use App\Models\Causa;
use App\Models\Diagnostico;
use App\Models\Medicamento;
use App\Models\MedicoPaciente;
use App\Models\MedicoParteDiario;
use App\Models\MedicoParteMedicamento;
use App\Filament\Pages\MedicoPartesDiarios;
use Livewire\Livewire;

beforeEach(function (): void {
    // Crear datos de catálogo necesarios
    $this->area = Area::query()->create(['nombre' => 'Cocina', 'activo' => true]);
    $this->causa = Causa::query()->create(['nombre' => 'Dolor de cabeza', 'activo' => true]);
    $this->diagnostico = Diagnostico::query()->create(['nombre' => 'Cefalea tensional', 'activo' => true]);
    $this->medicamento = Medicamento::query()->create(['nombre' => 'Paracetamol', 'activo' => true]);
    $this->medicamento2 = Medicamento::query()->create(['nombre' => 'Ibuprofeno', 'activo' => true]);
});

// ============================================================
// 1. CREAR ATENCIÓN
// ============================================================

it('crea una atención médica con datos válidos', function (): void {
    Livewire::test(MedicoPartesDiarios::class)
        ->set('fecha', now()->toDateString())
        ->set('nombres', 'Juan Pérez')
        ->set('edad', 30)
        ->set('area_id', $this->area->id)
        ->set('tipoPaciente', 'colaborador')
        ->set('causa_id', $this->causa->id)
        ->set('diagnostico_id', $this->diagnostico->id)
        ->set('observacion', 'Paciente estable')
        ->set('medicamentos', [
            ['medicamento_id' => $this->medicamento->id, 'cantidad' => 2],
        ])
        ->call('guardar')
        ->assertHasNoErrors();

    expect(MedicoParteDiario::query()->count())->toBe(1);
    $parte = MedicoParteDiario::query()->first();
    expect($parte->nombres)->toBe('JUAN PÉREZ');
    expect($parte->hash_unico)->not->toBeNull();

    // Verificar medicamento asociado
    expect($parte->medicamentos()->count())->toBe(1);
    expect($parte->medicamentos()->first()->cantidad)->toBe(2.0);
});

it('crea una atención para huésped con habitación', function (): void {
    Livewire::test(MedicoPartesDiarios::class)
        ->set('fecha', now()->toDateString())
        ->set('nombres', 'María Huésped')
        ->set('tipoPaciente', 'huesped')
        ->set('habitacion', '301')
        ->set('causa_id', $this->causa->id)
        ->set('medicamentos', [
            ['medicamento_id' => $this->medicamento->id, 'cantidad' => 1],
        ])
        ->call('guardar')
        ->assertHasNoErrors();

    $parte = MedicoParteDiario::query()->first();
    expect($parte->tipo_paciente)->toBe('huesped');
    expect($parte->habitacion)->toBe('301');
});

// ============================================================
// 2. VALIDACIÓN
// ============================================================

it('valida que nombres sea requerido', function (): void {
    Livewire::test(MedicoPartesDiarios::class)
        ->set('nombres', '')
        ->set('causa_id', $this->causa->id)
        ->set('medicamentos', [
            ['medicamento_id' => $this->medicamento->id, 'cantidad' => 1],
        ])
        ->call('guardar')
        ->assertHasErrors(['nombres' => 'required']);
});

it('valida que causa_id sea requerido', function (): void {
    Livewire::test(MedicoPartesDiarios::class)
        ->set('nombres', 'Juan')
        ->set('causa_id', null)
        ->set('medicamentos', [
            ['medicamento_id' => $this->medicamento->id, 'cantidad' => 1],
        ])
        ->call('guardar')
        ->assertHasErrors(['causa_id' => 'required']);
});

it('valida que habitación sea requerida para huéspedes', function (): void {
    Livewire::test(MedicoPartesDiarios::class)
        ->set('nombres', 'Huésped Sin Hab')
        ->set('tipoPaciente', 'huesped')
        ->set('causa_id', $this->causa->id)
        ->set('medicamentos', [
            ['medicamento_id' => $this->medicamento->id, 'cantidad' => 1],
        ])
        ->call('guardar')
        ->assertHasErrors(['habitacion' => 'required_if']);
});

it('rechaza guardar sin al menos un medicamento', function (): void {
    Livewire::test(MedicoPartesDiarios::class)
        ->set('nombres', 'Juan')
        ->set('causa_id', $this->causa->id)
        ->set('medicamentos', [
            ['medicamento_id' => null, 'cantidad' => 1],
        ])
        ->call('guardar')
        ->assertHasErrors(['medicamentos.0.medicamento_id']);
});

// ============================================================
// 3. EDITAR ATENCIÓN
// ============================================================

it('carga datos en el formulario al editar', function (): void {
    $parte = MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'JUAN PÉREZ',
        'edad' => 30,
        'area_id' => $this->area->id,
        'tipo_paciente' => 'colaborador',
        'causa_id' => $this->causa->id,
        'diagnostico_id' => $this->diagnostico->id,
        'observacion' => 'Test obs',
        'hash_unico' => hash('sha256', 'test-edit'),
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class)
        ->call('editar', $parte->id);

    expect($component->get('editandoId'))->toBe($parte->id);
    expect($component->get('nombres'))->toBe('JUAN PÉREZ');
    expect($component->get('observacion'))->toBe('Test obs');
});

it('actualiza una atención existente', function (): void {
    $parte = MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'ORIGINAL',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'test-update'),
    ]);

    MedicoParteMedicamento::query()->create([
        'parte_diario_id' => $parte->id,
        'medicamento_id' => $this->medicamento->id,
        'nombre_original' => $this->medicamento->nombre,
        'cantidad' => 1,
    ]);

    Livewire::test(MedicoPartesDiarios::class)
        ->call('editar', $parte->id)
        ->set('nombres', 'ACTUALIZADO')
        ->set('causa_id', $this->causa->id)
        ->set('medicamentos', [
            ['medicamento_id' => $this->medicamento2->id, 'cantidad' => 3],
        ])
        ->call('guardar')
        ->assertHasNoErrors();

    $parte->refresh();
    expect($parte->nombres)->toBe('ACTUALIZADO');
    expect($parte->medicamentos()->count())->toBe(1);
    expect($parte->medicamentos()->first()->medicamento_id)->toBe($this->medicamento2->id);
    expect($parte->medicamentos()->first()->cantidad)->toBe(3.0);
});

// ============================================================
// 4. ELIMINAR ATENCIÓN
// ============================================================

it('elimina una atención y sus medicamentos asociados', function (): void {
    $parte = MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'A ELIMINAR',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'test-delete'),
    ]);

    MedicoParteMedicamento::query()->create([
        'parte_diario_id' => $parte->id,
        'medicamento_id' => $this->medicamento->id,
        'nombre_original' => $this->medicamento->nombre,
        'cantidad' => 2,
    ]);

    expect(MedicoParteDiario::query()->count())->toBe(1);
    expect(MedicoParteMedicamento::query()->count())->toBe(1);

    Livewire::test(MedicoPartesDiarios::class)
        ->call('solicitarEliminar', $parte->id)
        ->assertSet('modalEliminarAbierto', true)
        ->assertSet('eliminandoId', $parte->id)
        ->call('confirmarEliminar')
        ->assertSet('modalEliminarAbierto', false);

    expect(MedicoParteDiario::query()->count())->toBe(0);
    expect(MedicoParteMedicamento::query()->count())->toBe(0);
});

it('puede cancelar la eliminación', function (): void {
    $parte = MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'NO ELIMINAR',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'test-cancel'),
    ]);

    Livewire::test(MedicoPartesDiarios::class)
        ->call('solicitarEliminar', $parte->id)
        ->assertSet('modalEliminarAbierto', true)
        ->call('cancelarEliminar')
        ->assertSet('modalEliminarAbierto', false)
        ->assertSet('eliminandoId', null);

    expect(MedicoParteDiario::query()->count())->toBe(1);
});

// ============================================================
// 5. FILTROS Y BÚSQUEDA
// ============================================================

it('filtra atenciones por fecha con "solo hoy"', function (): void {
    // Crear una atención de hoy
    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'HOY',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'hoy'),
    ]);

    // Crear una atención de ayer
    MedicoParteDiario::query()->create([
        'fecha' => now()->subDay()->toDateString(),
        'nombres' => 'AYER',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'ayer'),
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class);

    // Con "solo hoy" activo, solo muestra las de hoy
    expect($component->get('mostrarSoloHoy'))->toBeTrue();
    expect($component->get('totalPartes'))->toBe(1);
    expect($component->get('partes')->first()->nombres)->toBe('HOY');
});

it('filtra por área', function (): void {
    $otraArea = Area::query()->create(['nombre' => 'Limpieza', 'activo' => true]);

    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'Con Área Cocina',
        'area_id' => $this->area->id,
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'f1'),
    ]);

    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'Con Área Limpieza',
        'area_id' => $otraArea->id,
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'f2'),
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class)
        ->set('areaFiltroId', $this->area->id);

    expect($component->get('totalPartes'))->toBe(1);
    expect($component->get('partes')->first()->nombres)->toBe('Con Área Cocina');
});

it('filtra por tipo de paciente', function (): void {
    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'Colaborador A',
        'tipo_paciente' => 'colaborador',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'colab'),
    ]);

    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'Huésped B',
        'tipo_paciente' => 'huesped',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'huesp'),
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class)
        ->set('tipoPacienteFiltro', 'huesped');

    expect($component->get('totalPartes'))->toBe(1);
    expect($component->get('partes')->first()->nombres)->toBe('Huésped B');
});

it('busca por nombre de paciente', function (): void {
    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'CARLOS GARCÍA',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'carlos'),
    ]);

    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'ANA LÓPEZ',
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 'ana'),
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class)
        ->set('buscar', 'CARLOS');

    expect($component->get('totalPartes'))->toBe(1);
});

// ============================================================
// 6. ESTADÍSTICAS
// ============================================================

it('calcula correctamente las estadísticas del día', function (): void {
    $otraArea = Area::query()->create(['nombre' => 'Seguridad', 'activo' => true]);

    $causa2 = Causa::query()->create(['nombre' => 'Control general', 'activo' => true]);

    // 1 colaborador con certificado
    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'P1',
        'tipo_paciente' => 'colaborador',
        'area_id' => $this->area->id,
        'causa_id' => $this->causa->id,
        'hash_unico' => hash('sha256', 's1'),
    ]);

    // 2 huésped
    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'P2',
        'tipo_paciente' => 'huesped',
        'area_id' => null,
        'causa_id' => $causa2->id,
        'hash_unico' => hash('sha256', 's2'),
    ]);

    // 3 colaborador con certificado en otra área
    MedicoParteDiario::query()->create([
        'fecha' => now()->toDateString(),
        'nombres' => 'P3',
        'tipo_paciente' => 'colaborador',
        'area_id' => $otraArea->id,
        'causa_id' => $this->causa->id,
        'entidad_certificado_id' => 1,
        'hash_unico' => hash('sha256', 's3'),
    ]);

    $stats = Livewire::test(MedicoPartesDiarios::class)->get('estadisticasHoy');

    expect($stats['total'])->toBe(3);
    expect($stats['huespedes'])->toBe(1);
    expect($stats['colabs'])->toBe(2);
    expect($stats['conCert'])->toBe(1);
    expect($stats['areas'])->toBe(2); // Cocina + Seguridad
});

// ============================================================
// 7. BÚSQUEDA DE PACIENTES
// ============================================================

it('encuentra pacientes por nombre en la búsqueda', function (): void {
    MedicoPaciente::query()->create([
        'nombres' => 'ROBERTO FERNÁNDEZ',
        'cedula' => '1234567890',
        'tipo' => 'colaborador',
        'area_id' => $this->area->id,
    ]);

    MedicoPaciente::query()->create([
        'nombres' => 'DIANA SÁNCHEZ',
        'cedula' => '0987654321',
        'tipo' => 'huesped',
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class)
        ->set('buscarPaciente', 'ROBERTO');

    expect($component->get('pacientes'))->toHaveCount(1);
    expect($component->get('pacientes')->first()->nombres)->toBe('ROBERTO FERNÁNDEZ');
});

it('encuentra pacientes por cédula en la búsqueda', function (): void {
    MedicoPaciente::query()->create([
        'nombres' => 'X',
        'cedula' => '999888777',
        'tipo' => 'colaborador',
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class)
        ->set('buscarPaciente', '999888');

    expect($component->get('pacientes'))->toHaveCount(1);
});

// ============================================================
// 8. QUICK-CREATE PACIENTE
// ============================================================

it('crea un paciente rápidamente y lo selecciona', function (): void {
    Livewire::test(MedicoPartesDiarios::class)
        ->set('quickNombres', 'Nuevo Paciente')
        ->set('quickTipo', 'huesped')
        ->set('quickTelefono', '0991234567')
        ->call('quickGuardarPaciente')
        ->assertHasNoErrors();

    expect(MedicoPaciente::query()->count())->toBe(1);

    $paciente = MedicoPaciente::query()->first();
    expect($paciente->nombres)->toBe('NUEVO PACIENTE');
    expect($paciente->tipo)->toBe('huesped');
    expect($paciente->telefono)->toBe('0991234567');
});

// ============================================================
// 9. LIMPIAR FORMULARIO
// ============================================================

it('limpia el formulario correctamente', function (): void {
    $component = Livewire::test(MedicoPartesDiarios::class)
        ->set('nombres', 'Test')
        ->set('observacion', 'Nota')
        ->set('medicamentos', [
            ['medicamento_id' => $this->medicamento->id, 'cantidad' => 3],
        ])
        ->call('limpiarFormulario');

    expect($component->get('editandoId'))->toBeNull();
    expect($component->get('pacienteId'))->toBeNull();
    expect($component->get('nombres'))->toBe('');
    expect($component->get('observacion'))->toBeNull();
    expect($component->get('causa_id'))->toBeNull();
    expect($component->get('medicamentos'))->toHaveCount(1);
    expect($component->get('medicamentos')[0]['medicamento_id'])->toBeNull();
});

// ============================================================
// 10. AUTO-COMPLETADO DESDE PACIENTE SELECCIONADO
// ============================================================

it('auto-completa datos al seleccionar un paciente', function (): void {
    $paciente = MedicoPaciente::query()->create([
        'nombres' => 'PACIENTE EXISTENTE',
        'cedula' => '111222333',
        'edad' => 25,
        'area_id' => $this->area->id,
        'tipo' => 'colaborador',
    ]);

    $component = Livewire::test(MedicoPartesDiarios::class)
        ->set('pacienteId', $paciente->id);

    expect($component->get('nombres'))->toBe('PACIENTE EXISTENTE');
    expect($component->get('cedula'))->toBe('111222333');
    expect($component->get('edad'))->toBe(25);
    expect($component->get('area_id'))->toBe($this->area->id);
    expect($component->get('tipoPaciente'))->toBe('colaborador');
});
