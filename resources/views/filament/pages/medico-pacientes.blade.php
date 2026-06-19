<x-filament-panels::page>
    <div class="grid gap-4 xl:grid-cols-[380px_1fr]">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $editandoId ? 'Editar paciente' : 'Nuevo paciente' }}</h3>
            <form wire:submit.prevent="guardar" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                <input wire:model="cedula" placeholder="Cedula" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                <input wire:model="nombres" placeholder="Nombres completos" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                <input type="number" wire:model="edad" placeholder="Edad" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                <input wire:model="area" placeholder="Area" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                <input wire:model="cargo" placeholder="Cargo" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                <input type="date" wire:model="fecha_ingreso" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                <select wire:model="tipo" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <option value="colaborador">Colaborador</option>
                    <option value="aspirante">Aspirante</option>
                    <option value="externo">Externo</option>
                    <option value="paciente">Paciente</option>
                </select>
                <textarea wire:model="patologias" placeholder="Patologias" rows="2" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                <textarea wire:model="observaciones" placeholder="Observaciones" rows="2" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="activo" class="rounded border-gray-300"> Activo</label>
                <div class="flex gap-2">
                    <button class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white">Guardar</button>
                    <button type="button" wire:click="limpiarFormulario" class="rounded-lg border border-gray-200 px-4 py-2 text-sm dark:border-gray-700">Limpiar</button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div><h3 class="font-semibold text-gray-950 dark:text-white">Base de pacientes</h3><p class="text-xs text-gray-500">Colaboradores, aspirantes y externos</p></div>
                <input wire:model.live.debounce.400ms="buscar" placeholder="Buscar" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-950/50"><tr><th class="px-5 py-3 text-left text-xs text-gray-500">Nombre</th><th class="px-5 py-3 text-left text-xs text-gray-500">Area/Cargo</th><th class="px-5 py-3 text-left text-xs text-gray-500">Tipo</th><th class="px-5 py-3 text-right text-xs text-gray-500">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($this->pacientes as $p)
                            <tr><td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $p->nombres }}<p class="text-xs text-gray-500">{{ $p->cedula }}</p></td><td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $p->area ?: '-' }}<p class="text-xs text-gray-500">{{ $p->cargo }}</p></td><td class="px-5 py-3 text-gray-500">{{ $p->tipo }} · {{ $p->activo ? 'Activo' : 'Inactivo' }}</td><td class="px-5 py-3 text-right"><button wire:click="editar({{ $p->id }})" class="text-sky-600 hover:underline">Editar</button><button wire:click="alternar({{ $p->id }})" class="ml-3 text-gray-500 hover:underline">{{ $p->activo ? 'Desactivar' : 'Activar' }}</button></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
