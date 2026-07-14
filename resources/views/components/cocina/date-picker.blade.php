@props([
    'field' => 'fechaSeleccionada',
    'label' => 'Fecha',
    'placeholder' => 'Selecciona una fecha',
    'available' => [],
    'align' => 'right',
])

@php
    $availableJson = json_encode(array_values($available));
@endphp

<div
    x-data="cocinaDatePicker('{{ $field }}', @js($availableJson))"
    x-init="init()"
    x-effect="syncAvailable()"
    class="relative"
    x-cloak
>
    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>

    <button
        type="button"
        @click="toggle()"
        class="input mt-1 flex w-full items-center justify-between gap-2 text-left"
        :class="open ? 'ring-2 ring-ocean-200 dark:ring-ocean-700' : ''"
    >
        <span x-show="!selected" class="text-gray-400 dark:text-gray-500">{{ $placeholder }}</span>
        <span x-show="selected" class="font-medium text-gray-900 dark:text-white" x-text="selected ? formatDisplay(selected) : ''"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 13.5h13.5A2.25 2.25 0 0 1 21 18.75"/></svg>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-transition.origin.top
        class="absolute z-30 mt-2 w-64 rounded-2xl border border-gray-100 bg-white p-3 shadow-xl dark:border-gray-700 dark:bg-gray-900 {{ $align === 'right' ? 'right-0' : 'left-0' }}"
    >
        <div class="mb-2 flex items-center justify-between">
            <button type="button" @click="prevMonth()" class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="monthLabel"></span>
            <button type="button" @click="nextMonth()" class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
        </div>

        <div class="mb-1 grid grid-cols-7 gap-1 text-center text-[11px] font-medium uppercase text-gray-400">
            <template x-for="d in ['L','M','X','J','V','S','D']" :key="d"><span x-text="d"></span></template>
        </div>

        <div class="grid grid-cols-7 gap-1">
            <template x-for="(cell, i) in grid" :key="i">
                <div>
                    <button
                        type="button"
                        x-show="cell !== null"
                        @click="cell !== null && isAvailable(cell) ? pick(cell) : null"
                        :disabled="cell !== null && !isAvailable(cell)"
                        class="aspect-square w-full rounded-lg text-xs font-medium transition"
                        :class="cellClass(cell)"
                        x-text="cell ? new Date(cell + 'T00:00:00').getDate() : ''"
                    ></button>
                </div>
            </template>
        </div>

        @if (count($available) === 0)
            <p class="mt-2 text-center text-xs text-gray-400">No hay fechas disponibles.</p>
        @endif
    </div>
</div>

<script>
function cocinaDatePicker(field, available) {
    return {
        field: field,
        available: available,
        open: false,
        selected: null,
        viewYear: 0,
        viewMonth: 0,
        init() {
            this.syncAvailable();
            const sel = this.$wire[field];
            if (sel && available.includes(sel)) {
                this.selected = sel;
                const d = new Date(sel + 'T00:00:00');
                this.viewYear = d.getFullYear();
                this.viewMonth = d.getMonth();
            } else if (available.length > 0) {
                const first = available[0];
                const d = new Date(first + 'T00:00:00');
                this.viewYear = d.getFullYear();
                this.viewMonth = d.getMonth();
                if (!sel) {
                    this.selected = first;
                    this.$wire.call('setFecha', field, first);
                }
            }
        },
        syncAvailable() {
            if (this.$wire && this.$wire.fechasDisponiblesRaw && Array.isArray(this.$wire.fechasDisponiblesRaw)) {
                this.available = this.$wire.fechasDisponiblesRaw;
            }
        },
        toggle() {
            if (this.available.length === 0) return;
            this.open = !this.open;
            if (this.open) {
                const base = (this.selected && this.available.includes(this.selected))
                    ? this.selected
                    : (this.available.length ? this.available[0] : null);
                if (base) {
                    const d = new Date(base + 'T00:00:00');
                    this.viewYear = d.getFullYear();
                    this.viewMonth = d.getMonth();
                }
            }
        },
        isAvailable(day) {
            return this.available.includes(day);
        },
        pick(day) {
            this.selected = day;
            this.open = false;
            this.$wire.call('setFecha', this.field, day);
        },
        prevMonth() {
            if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
            else { this.viewMonth--; }
        },
        nextMonth() {
            if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
            else { this.viewMonth++; }
        },
        get monthLabel() {
            const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            return meses[this.viewMonth] + ' ' + this.viewYear;
        },
        get grid() {
            const firstDay = new Date(this.viewYear, this.viewMonth, 1);
            let offset = (firstDay.getDay() + 6) % 7; // Lunes = 0
            const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            const cells = [];
            for (let i = 0; i < offset; i++) cells.push(null);
            for (let d = 1; d <= daysInMonth; d++) {
                const y = this.viewYear;
                const m = String(this.viewMonth + 1).padStart(2, '0');
                const dd = String(d).padStart(2, '0');
                cells.push(y + '-' + m + '-' + dd);
            }
            while (cells.length % 7 !== 0) cells.push(null);
            return cells;
        },
        cellClass(cell) {
            if (cell === null) return '';
            if (!this.isAvailable(cell)) {
                return 'cursor-not-allowed text-gray-300 dark:text-gray-600';
            }
            if (cell === this.selected) {
                return 'bg-ocean-600 text-white shadow-sm dark:bg-ocean-500';
            }
            return 'bg-gray-50 text-gray-700 hover:bg-ocean-50 hover:text-ocean-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-ocean-900/40 dark:hover:text-ocean-200';
        },
        formatDisplay(day) {
            const [y, m, d] = day.split('-');
            const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
            return d + ' ' + meses[parseInt(m, 10) - 1] + ' ' + y;
        },
    };
}
</script>
