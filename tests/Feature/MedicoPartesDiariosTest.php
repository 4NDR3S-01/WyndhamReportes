<?php

namespace Tests\Feature;

use App\Filament\Pages\MedicoPartesDiarios;
use App\Models\Area;
use App\Models\Causa;
use App\Models\Diagnostico;
use App\Models\EntidadCertificado;
use App\Models\Medicamento;
use App\Models\MedicoPaciente;
use App\Models\MedicoParteDiario;
use App\Models\MedicoParteMedicamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicoPartesDiariosTest extends TestCase
{
    use RefreshDatabase;

    protected Area $area;

    protected Causa $causa;

    protected Diagnostico $diagnostico;

    protected Medicamento $medicamento;

    protected Medicamento $medicamento2;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear datos de catálogo necesarios
        $this->area = Area::query()->create(['nombre' => 'Cocina', 'activo' => true]);
        $this->causa = Causa::query()->create(['nombre' => 'Dolor de cabeza', 'activo' => true]);
        $this->diagnostico = Diagnostico::query()->create(['nombre' => 'Cefalea tensional', 'activo' => true]);
        $this->medicamento = Medicamento::query()->create(['nombre' => 'Paracetamol', 'activo' => true]);
        $this->medicamento2 = Medicamento::query()->create(['nombre' => 'Ibuprofeno', 'activo' => true]);
    }

    // ============================================================
    // 1. CREAR ATENCIÓN
    // ============================================================

    public function test_crea_una_atencion_medica_con_datos_validos(): void
    {
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

        $this->assertSame(1, MedicoParteDiario::query()->count());
        $parte = MedicoParteDiario::query()->first();
        $this->assertSame('JUAN PÉREZ', $parte->nombres);
        $this->assertNotNull($parte->hash_unico);

        // Verificar medicamento asociado
        $this->assertSame(1, $parte->medicamentos()->count());
        $this->assertEquals(2.0, $parte->medicamentos()->first()->cantidad);
    }

    public function test_crea_una_atencion_para_huesped_con_habitacion(): void
    {
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
        $this->assertSame('huesped', $parte->tipo_paciente);
        $this->assertSame('301', $parte->habitacion);
    }

    // ============================================================
    // 2. VALIDACIÓN
    // ============================================================

    public function test_valida_que_nombres_sea_requerido(): void
    {
        Livewire::test(MedicoPartesDiarios::class)
            ->set('nombres', '')
            ->set('causa_id', $this->causa->id)
            ->set('medicamentos', [
                ['medicamento_id' => $this->medicamento->id, 'cantidad' => 1],
            ])
            ->call('guardar')
            ->assertHasErrors(['nombres' => 'required']);
    }

    public function test_valida_que_causa_id_sea_requerido(): void
    {
        Livewire::test(MedicoPartesDiarios::class)
            ->set('nombres', 'Juan')
            ->set('causa_id', null)
            ->set('medicamentos', [
                ['medicamento_id' => $this->medicamento->id, 'cantidad' => 1],
            ])
            ->call('guardar')
            ->assertHasErrors(['causa_id' => 'required']);
    }

    public function test_valida_que_habitacion_sea_requerida_para_huespedes(): void
    {
        Livewire::test(MedicoPartesDiarios::class)
            ->set('nombres', 'Huésped Sin Hab')
            ->set('tipoPaciente', 'huesped')
            ->set('causa_id', $this->causa->id)
            ->set('medicamentos', [
                ['medicamento_id' => $this->medicamento->id, 'cantidad' => 1],
            ])
            ->call('guardar')
            ->assertHasErrors(['habitacion' => 'required_if']);
    }

    public function test_rechaza_guardar_una_linea_sin_cantidad(): void
    {
        Livewire::test(MedicoPartesDiarios::class)
            ->set('nombres', 'Juan')
            ->set('causa_id', $this->causa->id)
            ->set('medicamentos', [
                ['medicamento_id' => $this->medicamento->id, 'cantidad' => null],
            ])
            ->call('guardar')
            ->assertHasErrors(['medicamentos.0.cantidad']);
    }

    // ============================================================
    // 3. EDITAR ATENCIÓN
    // ============================================================

    public function test_carga_datos_en_el_formulario_al_editar(): void
    {
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

        $this->assertSame($parte->id, $component->get('editandoId'));
        $this->assertSame('JUAN PÉREZ', $component->get('nombres'));
        $this->assertSame('Test obs', $component->get('observacion'));
    }

    public function test_actualiza_una_atencion_existente(): void
    {
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
        $this->assertSame('ACTUALIZADO', $parte->nombres);
        $this->assertSame(1, $parte->medicamentos()->count());
        $primero = $parte->medicamentos()->first();
        $this->assertSame($this->medicamento2->id, $primero->medicamento_id);
        $this->assertEquals(3.0, $primero->cantidad);
    }

    // ============================================================
    // 4. ELIMINAR ATENCIÓN
    // ============================================================

    public function test_elimina_una_atencion_y_sus_medicamentos_asociados(): void
    {
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

        $this->assertSame(1, MedicoParteDiario::query()->count());
        $this->assertSame(1, MedicoParteMedicamento::query()->count());

        Livewire::test(MedicoPartesDiarios::class)
            ->call('solicitarEliminar', $parte->id)
            ->assertSet('modalEliminarAbierto', true)
            ->assertSet('eliminandoId', $parte->id)
            ->call('confirmarEliminar')
            ->assertSet('modalEliminarAbierto', false);

        $this->assertSame(0, MedicoParteDiario::query()->count());
        $this->assertSame(0, MedicoParteMedicamento::query()->count());
    }

    public function test_puede_cancelar_la_eliminacion(): void
    {
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

        $this->assertSame(1, MedicoParteDiario::query()->count());
    }

    // ============================================================
    // 5. FILTROS Y BÚSQUEDA
    // ============================================================

    public function test_filtra_atenciones_por_fecha_con_solo_hoy(): void
    {
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
        $this->assertTrue($component->get('mostrarSoloHoy'));
        $this->assertSame(1, $component->get('totalPartes'));
        $this->assertSame('HOY', $component->get('partes')->first()->nombres);
    }

    public function test_filtra_por_area(): void
    {
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

        $this->assertSame(1, $component->get('totalPartes'));
        $this->assertSame('Con Área Cocina', $component->get('partes')->first()->nombres);
    }

    public function test_filtra_por_tipo_de_paciente(): void
    {
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

        $this->assertSame(1, $component->get('totalPartes'));
        $this->assertSame('Huésped B', $component->get('partes')->first()->nombres);
    }

    public function test_busca_por_nombre_de_paciente(): void
    {
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

        $this->assertSame(1, $component->get('totalPartes'));
    }

    // ============================================================
    // 6. ESTADÍSTICAS
    // ============================================================

    public function test_calcula_correctamente_las_estadisticas_del_dia(): void
    {
        $otraArea = Area::query()->create(['nombre' => 'Seguridad', 'activo' => true]);

        $causa2 = Causa::query()->create(['nombre' => 'Control general', 'activo' => true]);

        $entidadCertificado = EntidadCertificado::query()->create(['nombre' => 'IESS', 'activo' => true]);

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
            'entidad_certificado_id' => $entidadCertificado->id,
            'hash_unico' => hash('sha256', 's3'),
        ]);

        $stats = Livewire::test(MedicoPartesDiarios::class)->get('estadisticasHoy');

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['huespedes']);
        $this->assertSame(2, $stats['colabs']);
        $this->assertSame(1, $stats['conCert']);
        $this->assertSame(2, $stats['areas']); // Cocina + Seguridad
    }

    // ============================================================
    // 7. BÚSQUEDA DE PACIENTES
    // ============================================================

    public function test_encuentra_pacientes_por_nombre_en_la_busqueda(): void
    {
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

        $this->assertCount(1, $component->get('pacientes'));
        $this->assertSame('ROBERTO FERNÁNDEZ', $component->get('pacientes')->first()->nombres);
    }

    public function test_encuentra_pacientes_por_cedula_en_la_busqueda(): void
    {
        MedicoPaciente::query()->create([
            'nombres' => 'X',
            'cedula' => '999888777',
            'tipo' => 'colaborador',
        ]);

        $component = Livewire::test(MedicoPartesDiarios::class)
            ->set('buscarPaciente', '999888');

        $this->assertCount(1, $component->get('pacientes'));
    }

    // ============================================================
    // 8. QUICK-CREATE PACIENTE
    // ============================================================

    public function test_crea_un_paciente_rapidamente_y_lo_selecciona(): void
    {
        Livewire::test(MedicoPartesDiarios::class)
            ->set('qNombres', 'Nuevo Paciente')
            ->set('qTipo', 'huesped')
            ->set('qTelefono', '0991234567')
            ->call('quickGuardarPaciente')
            ->assertHasNoErrors();

        $this->assertSame(1, MedicoPaciente::query()->count());

        $paciente = MedicoPaciente::query()->first();
        $this->assertSame('NUEVO PACIENTE', $paciente->nombres);
        $this->assertSame('huesped', $paciente->tipo);
        $this->assertSame('0991234567', $paciente->telefono);
    }

    // ============================================================
    // 9. LIMPIAR FORMULARIO
    // ============================================================

    public function test_limpia_el_formulario_correctamente(): void
    {
        $component = Livewire::test(MedicoPartesDiarios::class)
            ->set('nombres', 'Test')
            ->set('observacion', 'Nota')
            ->set('medicamentos', [
                ['medicamento_id' => $this->medicamento->id, 'cantidad' => 3],
            ])
            ->call('limpiarFormulario');

        $this->assertNull($component->get('editandoId'));
        $this->assertNull($component->get('pacienteId'));
        $this->assertSame('', $component->get('nombres'));
        $this->assertNull($component->get('observacion'));
        $this->assertNull($component->get('causa_id'));
        $this->assertCount(0, $component->get('medicamentos'));
    }

    // ============================================================
    // 10. AUTO-COMPLETADO DESDE PACIENTE SELECCIONADO
    // ============================================================

    public function test_auto_completa_datos_al_seleccionar_un_paciente(): void
    {
        $paciente = MedicoPaciente::query()->create([
            'nombres' => 'PACIENTE EXISTENTE',
            'cedula' => '111222333',
            'edad' => 25,
            'area_id' => $this->area->id,
            'tipo' => 'colaborador',
        ]);

        $component = Livewire::test(MedicoPartesDiarios::class)
            ->set('pacienteId', $paciente->id);

        $this->assertSame('PACIENTE EXISTENTE', $component->get('nombres'));
        $this->assertSame('111222333', $component->get('cedula'));
        $this->assertSame(25, $component->get('edad'));
        $this->assertSame($this->area->id, $component->get('area_id'));
        $this->assertSame('colaborador', $component->get('tipoPaciente'));
    }
}
