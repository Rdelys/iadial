{{-- resources/views/calendrier.blade.php --}}
@extends('layouts.app')

@section('title', "Calendrier des rendez-vous - IADial")

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 sm:pt-20 pb-20 sm:pb-28"
    id="cal-page" data-appointments="{{ json_encode($appointments) }}">

    <div class="hero-in-1 text-center max-w-2xl mx-auto mb-10 sm:mb-12">
        <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-xs text-sky-300 mb-5">
            <span class="listen-wave" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></span>
            Vue d'ensemble &middot; lecture seule
        </span>
        <h1 class="font-display text-2xl sm:text-3xl md:text-4xl font-semibold tracking-tight">Calendrier des rendez-vous</h1>
        <p class="mt-4 text-white/50 text-sm sm:text-base leading-relaxed">
            Tous les rendez-vous confirmés par les réceptionnistes IA IADial, tous essais confondus. Cette page est un simple
            aperçu &mdash; pour réserver un créneau, testez votre propre assistant depuis la
            <a href="{{ route('iarecep.index') }}" class="text-sky-300 hover:text-sky-200 underline underline-offset-2">page d'essai gratuit</a>.
        </p>
    </div>

    <div class="hero-in-2 rounded-2xl sm:rounded-3xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-4 sm:p-6 lg:p-8">

        {{-- En-tête calendrier --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <button type="button" id="cal-prev" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-white/60 hover:text-white hover:border-sky-400/40 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" id="cal-next" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-white/60 hover:text-white hover:border-sky-400/40 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
            <span id="cal-label" class="text-base sm:text-xl font-display font-semibold text-white"></span>
            <button type="button" id="cal-today" class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-white/60 hover:text-white hover:border-sky-400/40 transition">
                Aujourd'hui
            </button>
        </div>

        {{-- Jours de la semaine --}}
        <div class="grid grid-cols-7 gap-1.5 text-center text-xs font-medium text-white/40 mb-2">
            <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
        </div>

        {{-- Grille des jours --}}
        <div id="cal-grid" class="grid grid-cols-7 gap-1.5"></div>

        {{-- Légende --}}
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 text-xs text-white/40">
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-white/20"></span> Aucun rendez-vous</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-400"></span> Un ou plusieurs rendez-vous</span>
        </div>

        {{-- Détail du jour sélectionné (lecture seule) --}}
        <div id="cal-day-detail" class="hidden border-t border-white/10 pt-5 mt-6">
            <p id="cal-day-detail-title" class="text-sm font-medium text-white/80 mb-3"></p>
            <div id="cal-day-detail-list" class="grid sm:grid-cols-2 gap-2"></div>
        </div>

        {{-- Liste complète de tous les rendez-vous de la base --}}
        <div class="border-t border-white/10 pt-5 mt-8">
            <h4 class="text-sm font-medium text-white/70 mb-3">
                Tous les rendez-vous <span class="text-white/30">({{ $appointments->count() }})</span>
            </h4>
            <div class="grid sm:grid-cols-2 gap-2">
                @forelse ($appointments as $a)
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-white/5 border border-white/10 px-4 py-2.5 text-sm">
                        <span class="text-white/80">{{ $a['date_fr'] }} à {{ $a['time'] }}</span>
                        <span class="text-white/40 text-xs text-right">
                            {{ $a['full_name'] }}
                            @if ($a['company'])
                                <span class="block text-white/25">{{ $a['company'] }}</span>
                            @endif
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-white/30">Aucun rendez-vous pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const appointments = JSON.parse(document.getElementById('cal-page').dataset.appointments || '[]');

    const grid = document.getElementById('cal-grid');
    const label = document.getElementById('cal-label');
    const prevBtn = document.getElementById('cal-prev');
    const nextBtn = document.getElementById('cal-next');
    const todayBtn = document.getElementById('cal-today');
    const detailBox = document.getElementById('cal-day-detail');
    const detailTitle = document.getElementById('cal-day-detail-title');
    const detailList = document.getElementById('cal-day-detail-list');

    const MONTHS_FR = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const MAX_CHIPS_PER_DAY = 2;

    let viewDate = new Date();
    viewDate.setDate(1);
    let selectedDate = null;

    function fmtDate(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function appointmentsForDate(dateStr) {
        return appointments
            .filter(a => a.date === dateStr)
            .sort((a, b) => a.time.localeCompare(b.time));
    }

    function renderCalendar() {
        grid.innerHTML = '';
        label.textContent = `${MONTHS_FR[viewDate.getMonth()]} ${viewDate.getFullYear()}`;

        const year = viewDate.getFullYear();
        const month = viewDate.getMonth();
        const firstDay = new Date(year, month, 1);
        let startOffset = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayStr = fmtDate(today);

        for (let i = 0; i < startOffset; i++) {
            grid.appendChild(document.createElement('div'));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const cellDate = new Date(year, month, day);
            const dateStr = fmtDate(cellDate);
            const isToday = dateStr === todayStr;
            const isSelected = selectedDate === dateStr;
            const dayAppointments = appointmentsForDate(dateStr);
            const hasAppointments = dayAppointments.length > 0;

            const cell = document.createElement('button');
            cell.type = 'button';

            let cls = 'group relative flex flex-col items-start min-h-[68px] sm:min-h-[92px] rounded-xl p-1.5 sm:p-2 text-left transition ';
            if (isSelected) {
                cls += 'bg-gradient-to-br from-sky-400/20 to-indigo-500/20 border-2 border-sky-400 cursor-pointer';
            } else if (hasAppointments) {
                cls += 'bg-sky-400/[0.06] border border-sky-400/25 hover:border-sky-400/50 cursor-pointer';
            } else {
                cls += 'bg-white/[0.02] border border-white/10 hover:border-white/20 cursor-pointer';
            }
            cell.className = cls;

            const dayNum = document.createElement('span');
            dayNum.textContent = day;
            dayNum.className = isToday
                ? 'inline-flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-sky-400 text-black text-xs font-semibold'
                : 'text-xs sm:text-sm font-medium text-white/70';
            cell.appendChild(dayNum);

            if (hasAppointments) {
                const chipsWrap = document.createElement('div');
                chipsWrap.className = 'mt-1 w-full space-y-0.5 hidden sm:block';

                dayAppointments.slice(0, MAX_CHIPS_PER_DAY).forEach(a => {
                    const chip = document.createElement('div');
                    chip.className = 'truncate rounded px-1.5 py-0.5 text-[10px] leading-tight bg-sky-400/15 text-sky-200';
                    chip.textContent = `${a.time} · ${a.full_name}`;
                    chipsWrap.appendChild(chip);
                });

                if (dayAppointments.length > MAX_CHIPS_PER_DAY) {
                    const more = document.createElement('div');
                    more.className = 'text-[10px] text-white/40 px-1.5';
                    more.textContent = `+${dayAppointments.length - MAX_CHIPS_PER_DAY} autre(s)`;
                    chipsWrap.appendChild(more);
                }

                cell.appendChild(chipsWrap);

                const dot = document.createElement('span');
                dot.className = 'sm:hidden absolute bottom-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-sky-400';
                cell.appendChild(dot);
            }

            cell.addEventListener('click', () => selectDate(dateStr));
            grid.appendChild(cell);
        }
    }

    function selectDate(dateStr) {
        selectedDate = dateStr;
        renderCalendar();

        const [y, m, d] = dateStr.split('-');
        const dayAppointments = appointmentsForDate(dateStr);

        detailBox.classList.remove('hidden');
        detailTitle.textContent = dayAppointments.length
            ? `${d}/${m}/${y} — ${dayAppointments.length} rendez-vous`
            : `${d}/${m}/${y} — aucun rendez-vous`;

        detailList.innerHTML = '';
        dayAppointments.forEach(a => {
            const item = document.createElement('div');
            item.className = 'flex items-center justify-between rounded-lg bg-white/5 border border-white/10 px-4 py-2.5 text-sm';
            item.innerHTML = `
                <span class="text-sky-300 font-medium">${a.time}</span>
                <span class="text-white/60 text-right">${a.full_name}${a.company ? `<span class="block text-white/30 text-xs">${a.company}</span>` : ''}</span>
            `;
            detailList.appendChild(item);
        });
    }

    prevBtn.addEventListener('click', () => {
        viewDate.setMonth(viewDate.getMonth() - 1);
        renderCalendar();
    });
    nextBtn.addEventListener('click', () => {
        viewDate.setMonth(viewDate.getMonth() + 1);
        renderCalendar();
    });
    todayBtn.addEventListener('click', () => {
        viewDate = new Date();
        viewDate.setDate(1);
        renderCalendar();
    });

    renderCalendar();
})();
</script>
@endsection