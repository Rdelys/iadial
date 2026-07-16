@extends('layouts.admin')

@section('title', 'Rendez-vous')

@section('content')

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <button id="cal-today" class="px-3 py-1.5 text-sm rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-800 transition">
                    Aujourd'hui
                </button>
                <div class="flex items-center gap-1">
                    <button id="cal-prev" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button id="cal-next" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
                <h2 id="cal-title" class="text-lg font-semibold capitalize"></h2>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-400"></span> Rendez-vous confirmé</span>
                <span class="ml-3 text-slate-600">{{ $appointments->count() }} au total</span>
            </div>
        </div>

        {{-- Days of week header --}}
        <div class="grid grid-cols-7 border-b border-slate-800 text-xs text-slate-500 uppercase tracking-wide">
            @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $day)
                <div class="px-3 py-2 text-center border-r border-slate-800 last:border-r-0">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div id="cal-grid" class="grid grid-cols-7"></div>
    </div>

    {{-- Day detail modal --}}
    <div id="day-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center px-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                <h3 id="day-modal-title" class="font-semibold"></h3>
                <button id="day-modal-close" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="day-modal-list" class="p-4 space-y-2 overflow-y-auto"></div>
        </div>
    </div>

    {{-- Appointment detail modal --}}
    <div id="appt-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center px-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-indigo-400"></i>
                    Détails du rendez-vous
                </h3>
                <button id="appt-modal-close" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="appt-modal-body" class="p-6 space-y-4"></div>
        </div>
    </div>

@endsection

@push('head')
<style>
    .cal-cell { min-height: 108px; }
    @media (max-width: 768px) { .cal-cell { min-height: 72px; } }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const appointments = @json($appointments);

    // Group appointments by date (YYYY-MM-DD) for O(1) lookup
    const byDate = {};
    appointments.forEach(a => {
        (byDate[a.date] ||= []).push(a);
    });
    Object.values(byDate).forEach(list => list.sort((a, b) => a.time.localeCompare(b.time)));

    const monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    let current = new Date();
    current.setDate(1);

    const titleEl = document.getElementById('cal-title');
    const gridEl = document.getElementById('cal-grid');

    function fmt(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function render() {
        titleEl.textContent = monthNames[current.getMonth()] + ' ' + current.getFullYear();
        gridEl.innerHTML = '';

        const year = current.getFullYear();
        const month = current.getMonth();
        const firstOfMonth = new Date(year, month, 1);
        // Monday-first offset
        const startOffset = (firstOfMonth.getDay() + 6) % 7;
        const gridStart = new Date(year, month, 1 - startOffset);

        const today = new Date();
        const todayStr = fmt(today);

        for (let i = 0; i < 42; i++) {
            const cellDate = new Date(gridStart);
            cellDate.setDate(gridStart.getDate() + i);
            const dateStr = fmt(cellDate);
            const isCurrentMonth = cellDate.getMonth() === month;
            const isToday = dateStr === todayStr;
            const dayAppointments = byDate[dateStr] || [];

            const cell = document.createElement('div');
            cell.className = 'cal-cell border-r border-b border-slate-800 last:border-r-0 p-2 flex flex-col gap-1 cursor-pointer transition hover:bg-slate-800/40 '
                + (isCurrentMonth ? '' : 'bg-slate-950/40');

            const numWrap = document.createElement('div');
            numWrap.className = 'flex items-center justify-between';

            const num = document.createElement('span');
            num.textContent = cellDate.getDate();
            num.className = 'text-xs font-medium ' + (isToday
                ? 'bg-indigo-500 text-white w-6 h-6 flex items-center justify-center rounded-full'
                : (isCurrentMonth ? 'text-slate-300' : 'text-slate-600'));
            numWrap.appendChild(num);

            if (dayAppointments.length > 0) {
                const count = document.createElement('span');
                count.textContent = dayAppointments.length;
                count.className = 'text-[10px] text-slate-500';
                numWrap.appendChild(count);
            }

            cell.appendChild(numWrap);

            const visible = dayAppointments.slice(0, 2);
            visible.forEach(a => {
                const pill = document.createElement('button');
                pill.type = 'button';
                pill.className = 'text-left text-[11px] leading-tight px-1.5 py-1 rounded-md bg-indigo-500/15 text-indigo-300 hover:bg-indigo-500/25 transition truncate';
                pill.textContent = a.time + ' · ' + a.full_name;
                pill.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openApptModal(a);
                });
                cell.appendChild(pill);
            });

            if (dayAppointments.length > 2) {
                const more = document.createElement('span');
                more.className = 'text-[11px] text-slate-500 px-1.5';
                more.textContent = '+' + (dayAppointments.length - 2) + ' autre(s)';
                cell.appendChild(more);
            }

            if (dayAppointments.length > 0) {
                cell.addEventListener('click', () => openDayModal(dateStr, cellDate, dayAppointments));
            }

            gridEl.appendChild(cell);
        }
    }

    // --- Day modal ---
    const dayModal = document.getElementById('day-modal');
    const dayModalTitle = document.getElementById('day-modal-title');
    const dayModalList = document.getElementById('day-modal-list');

    function openDayModal(dateStr, dateObj, list) {
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        dayModalTitle.textContent = dateObj.toLocaleDateString('fr-FR', options);
        dayModalList.innerHTML = '';

        list.forEach(a => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-slate-950/60 border border-slate-800 hover:border-indigo-500/50 transition text-left';
            row.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500/15 flex items-center justify-center text-indigo-300 shrink-0">
                        <i class="fa-solid fa-clock text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">${a.full_name}</p>
                        <p class="text-xs text-slate-500">${a.time}</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-slate-600"></i>
            `;
            row.addEventListener('click', () => openApptModal(a));
            dayModalList.appendChild(row);
        });

        dayModal.classList.remove('hidden');
        dayModal.classList.add('flex');
    }

    document.getElementById('day-modal-close').addEventListener('click', () => {
        dayModal.classList.add('hidden');
        dayModal.classList.remove('flex');
    });
    dayModal.addEventListener('click', (e) => { if (e.target === dayModal) dayModal.classList.add('hidden'), dayModal.classList.remove('flex'); });

    // --- Appointment detail modal ---
    const apptModal = document.getElementById('appt-modal');
    const apptModalBody = document.getElementById('appt-modal-body');

    function row(icon, label, value) {
        return `
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 shrink-0 mt-0.5">
                    <i class="fa-solid ${icon} text-xs"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">${label}</p>
                    <p class="text-sm text-slate-200 break-words">${value || '—'}</p>
                </div>
            </div>
        `;
    }

    function openApptModal(a) {
        const [y, m, d] = a.date.split('-');
        apptModalBody.innerHTML = `
            <div class="flex items-center gap-3 pb-2">
                <div class="w-11 h-11 rounded-full bg-indigo-500/15 flex items-center justify-center text-indigo-300 font-semibold">
                    ${a.full_name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <p class="font-medium">${a.full_name}</p>
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] bg-indigo-500/10 text-indigo-300">${a.source}</span>
                </div>
            </div>
            ${row('fa-calendar-day', 'Date', d + '/' + m + '/' + y)}
            ${row('fa-clock', 'Heure', a.time)}
            ${row('fa-phone', 'Téléphone', a.phone)}
            ${row('fa-envelope', 'Email', a.email)}
            ${row('fa-note-sticky', 'Motif', a.notes)}
            ${row('fa-hourglass-half', 'Réservé le', a.created_at)}
        `;
        apptModal.classList.remove('hidden');
        apptModal.classList.add('flex');
    }

    document.getElementById('appt-modal-close').addEventListener('click', () => {
        apptModal.classList.add('hidden');
        apptModal.classList.remove('flex');
    });
    apptModal.addEventListener('click', (e) => { if (e.target === apptModal) apptModal.classList.add('hidden'), apptModal.classList.remove('flex'); });

    // --- Navigation ---
    document.getElementById('cal-prev').addEventListener('click', () => {
        current.setMonth(current.getMonth() - 1);
        render();
    });
    document.getElementById('cal-next').addEventListener('click', () => {
        current.setMonth(current.getMonth() + 1);
        render();
    });
    document.getElementById('cal-today').addEventListener('click', () => {
        current = new Date();
        current.setDate(1);
        render();
    });

    render();
})();
</script>
@endpush