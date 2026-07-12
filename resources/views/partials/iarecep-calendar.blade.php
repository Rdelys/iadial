{{-- resources/views/partials/iarecep-calendar.blade.php --}}

{{-- Breakout : ce calendrier s'affiche en grand, indépendamment de la largeur du conteneur parent (comme Google Agenda) --}}
<div class="relative left-1/2 right-1/2 -mx-[50vw] w-screen px-4 sm:px-6">
<div class="max-w-5xl mx-auto rounded-2xl sm:rounded-3xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-4 sm:p-6 lg:p-8">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h3 class="text-lg sm:text-xl font-display font-semibold text-white">📅 Votre calendrier de rendez-vous</h3>
            <p class="text-sm text-white/50 mt-1">Chaque réservation faite par votre réceptionniste IA apparaît ici automatiquement.</p>
        </div>
    </div>

    {{-- Bannière : les 2 options --}}
    <div class="rounded-xl border border-sky-400/20 bg-sky-400/[0.06] px-4 py-3 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-sky-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-xs sm:text-sm text-white/60 leading-relaxed">
            Ce que vous voyez ici est le <span class="text-sky-300 font-medium">calendrier intégré IADial</span>, inclus par défaut.
            Vous pouvez aussi choisir de connecter <span class="text-indigo-300 font-medium">votre propre agenda</span> (Google Agenda, Outlook, iCloud…) :
            votre réceptionniste IA y ajoutera directement les rendez-vous, sans double calendrier à gérer.
        </p>
    </div>

    <div class="grid lg:grid-cols-[1fr_320px] gap-6 lg:gap-8">

        {{-- ===== Grille du mois ===== --}}
        <div>
            {{-- En-tête calendrier --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <button type="button" id="iarecep-cal-prev" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-white/60 hover:text-white hover:border-sky-400/40 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" id="iarecep-cal-next" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-white/60 hover:text-white hover:border-sky-400/40 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <span id="iarecep-cal-label" class="text-base sm:text-lg font-display font-semibold text-white"></span>
                <button type="button" id="iarecep-cal-today" class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-white/60 hover:text-white hover:border-sky-400/40 transition">
                    Aujourd'hui
                </button>
            </div>

            {{-- Jours de la semaine --}}
            <div class="grid grid-cols-7 gap-1.5 text-center text-xs font-medium text-white/40 mb-2">
                <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
            </div>

            {{-- Grille des jours --}}
            <div id="iarecep-cal-grid" class="grid grid-cols-7 gap-1.5"></div>

            {{-- Légende --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 text-xs text-white/40">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-400"></span> Disponible</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Partiellement réservé</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-400"></span> Complet</span>
            </div>
        </div>

        {{-- ===== Panneau latéral : détail du jour + réservation ===== --}}
        <div class="lg:border-l lg:border-white/10 lg:pl-6">
            <div id="iarecep-cal-empty-state" class="text-sm text-white/40 leading-relaxed">
                Sélectionnez une date dans le calendrier pour voir les rendez-vous déjà pris ou en réserver un nouveau.
            </div>

            <div id="iarecep-cal-booking" class="hidden">
                <p class="text-sm text-white/70 mb-3">
                    <span id="iarecep-cal-selected-date" class="text-sky-300 font-semibold"></span>
                </p>

                {{-- Rendez-vous déjà présents ce jour-là --}}
                <div id="iarecep-cal-day-events" class="space-y-1.5 mb-4"></div>

                <div id="iarecep-cal-slots" class="grid grid-cols-4 gap-2 mb-5"></div>

                <form id="iarecep-cal-form" class="hidden space-y-3">
                    @csrf
                    <input type="hidden" id="iarecep-cal-time-input" name="time">
                    <div>
                        <label class="block text-xs text-white/60 mb-1.5">Nom complet</label>
                        <input type="text" name="full_name" required placeholder="Jean Dupont"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
                    </div>
                    <div>
                        <label class="block text-xs text-white/60 mb-1.5">Téléphone (optionnel)</label>
                        <input type="tel" name="phone" placeholder="+261 34 00 000 00"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
                    </div>
                    <div>
                        <label class="block text-xs text-white/60 mb-1.5">Motif (optionnel)</label>
                        <input type="text" name="notes" placeholder="Ex : détartrage, contrôle..."
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
                    </div>
                    <button type="submit"
                        class="btn-shine w-full rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-black shadow-[0_0_20px_rgba(52,226,192,0.35)] hover:shadow-[0_0_30px_rgba(52,226,192,0.55)] transition-shadow">
                        Confirmer le rendez-vous
                    </button>
                    <p id="iarecep-cal-error" class="hidden text-xs text-red-400 text-center"></p>
                </form>
            </div>
        </div>
    </div>

    {{-- Liste complète des rendez-vous déjà pris --}}
    <div class="border-t border-white/10 pt-5 mt-8">
        <h4 class="text-sm font-medium text-white/70 mb-3">Tous les rendez-vous réservés</h4>
        <div id="iarecep-cal-list" class="grid sm:grid-cols-2 gap-2">
            <p id="iarecep-cal-list-empty" class="text-xs text-white/30">Aucun rendez-vous pour le moment.</p>
        </div>
    </div>

</div>
</div>

<script>
(function () {
    const grid = document.getElementById('iarecep-cal-grid');
    const label = document.getElementById('iarecep-cal-label');
    const prevBtn = document.getElementById('iarecep-cal-prev');
    const nextBtn = document.getElementById('iarecep-cal-next');
    const todayBtn = document.getElementById('iarecep-cal-today');
    const emptyState = document.getElementById('iarecep-cal-empty-state');
    const bookingBox = document.getElementById('iarecep-cal-booking');
    const selectedDateEl = document.getElementById('iarecep-cal-selected-date');
    const dayEventsBox = document.getElementById('iarecep-cal-day-events');
    const slotsBox = document.getElementById('iarecep-cal-slots');
    const form = document.getElementById('iarecep-cal-form');
    const timeInput = document.getElementById('iarecep-cal-time-input');
    const errorEl = document.getElementById('iarecep-cal-error');
    const listBox = document.getElementById('iarecep-cal-list');
    const listEmpty = document.getElementById('iarecep-cal-list-empty');

    const MONTHS_FR = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const SLOTS = ['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00'];
    const MAX_CHIPS_PER_DAY = 2;

    let viewDate = new Date();
    viewDate.setDate(1);
    let selectedDate = null;
    let bookedAppointments = []; // [{date, time, full_name}]

    function fmtDate(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function appointmentsForDate(dateStr) {
        return bookedAppointments
            .filter(a => a.date === dateStr)
            .sort((a, b) => a.time.localeCompare(b.time));
    }

    function bookedTimesFor(dateStr) {
        return appointmentsForDate(dateStr).map(a => a.time);
    }

    function renderCalendar() {
        grid.innerHTML = '';
        label.textContent = `${MONTHS_FR[viewDate.getMonth()]} ${viewDate.getFullYear()}`;

        const year = viewDate.getFullYear();
        const month = viewDate.getMonth();
        const firstDay = new Date(year, month, 1);
        // Lundi = 0 ... Dimanche = 6
        let startOffset = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayStr = fmtDate(today);

        for (let i = 0; i < startOffset; i++) {
            const empty = document.createElement('div');
            grid.appendChild(empty);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const cellDate = new Date(year, month, day);
            const dateStr = fmtDate(cellDate);
            const isPast = cellDate < today;
            const isToday = dateStr === todayStr;

            const dayAppointments = appointmentsForDate(dateStr);
            const takenTimes = dayAppointments.map(a => a.time);
            const isFullyBooked = takenTimes.length >= SLOTS.length;
            const isPartiallyBooked = takenTimes.length > 0 && !isFullyBooked;
            const isSelected = selectedDate === dateStr;

            const cell = document.createElement('button');
            cell.type = 'button';
            cell.disabled = isPast || isFullyBooked;

            let cls = 'group relative flex flex-col items-start min-h-[68px] sm:min-h-[92px] rounded-xl p-1.5 sm:p-2 text-left transition ';

            if (isPast) {
                cls += 'text-white/20 cursor-not-allowed bg-white/[0.015] border border-white/5';
            } else if (isSelected) {
                cls += 'bg-gradient-to-br from-sky-400/20 to-indigo-500/20 border-2 border-sky-400';
            } else if (isFullyBooked) {
                cls += 'bg-rose-500/10 border border-rose-400/40 text-rose-300 cursor-not-allowed';
            } else if (isPartiallyBooked) {
                cls += 'bg-amber-400/10 border border-amber-400/30 hover:border-amber-400/60 cursor-pointer';
            } else {
                cls += 'bg-white/5 border border-white/10 hover:border-sky-400/40 cursor-pointer';
            }

            cell.className = cls;

            const dayNum = document.createElement('span');
            dayNum.textContent = day;
            dayNum.className = isToday
                ? 'inline-flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-sky-400 text-black text-xs font-semibold'
                : 'text-xs sm:text-sm font-medium ' + (isPast ? 'text-white/20' : 'text-white/80');
            cell.appendChild(dayNum);

            if (!isPast && dayAppointments.length > 0) {
                const chipsWrap = document.createElement('div');
                chipsWrap.className = 'mt-1 w-full space-y-0.5 hidden sm:block';

                dayAppointments.slice(0, MAX_CHIPS_PER_DAY).forEach(a => {
                    const chip = document.createElement('div');
                    chip.className = 'truncate rounded px-1.5 py-0.5 text-[10px] leading-tight bg-white/10 text-white/70';
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

                // Sur mobile : un simple point indicateur pour ne pas surcharger
                const dot = document.createElement('span');
                dot.className = 'sm:hidden absolute bottom-1.5 right-1.5 w-1.5 h-1.5 rounded-full ' +
                    (isFullyBooked ? 'bg-rose-400' : 'bg-amber-400');
                cell.appendChild(dot);
            }

            cell.title = isFullyBooked
                ? 'Toutes les heures sont réservées'
                : (isPartiallyBooked ? `${takenTimes.length} créneau(x) déjà réservé(s)` : '');

            if (!isPast && !isFullyBooked) {
                cell.addEventListener('click', () => selectDate(dateStr));
            }

            grid.appendChild(cell);
        }
    }

    function selectDate(dateStr) {
        selectedDate = dateStr;
        renderCalendar();

        const [y, m, d] = dateStr.split('-');
        selectedDateEl.textContent = `${d}/${m}/${y}`;
        emptyState.classList.add('hidden');
        bookingBox.classList.remove('hidden');
        form.classList.add('hidden');
        errorEl.classList.add('hidden');

        // Rendez-vous déjà présents ce jour-là
        const dayAppointments = appointmentsForDate(dateStr);
        dayEventsBox.innerHTML = '';
        if (dayAppointments.length > 0) {
            const heading = document.createElement('p');
            heading.className = 'text-xs text-white/40 mb-1';
            heading.textContent = 'Déjà réservé :';
            dayEventsBox.appendChild(heading);

            dayAppointments.forEach(a => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-xs';
                row.innerHTML = `<span class="text-white/70 font-medium">${a.time}</span><span class="text-white/40">${a.full_name}</span>`;
                dayEventsBox.appendChild(row);
            });
        }

        const taken = bookedTimesFor(dateStr);
        slotsBox.innerHTML = '';

        SLOTS.forEach(time => {
            const isTaken = taken.includes(time);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = time;
            btn.disabled = isTaken;
            btn.className = isTaken
                ? 'rounded-lg px-2 py-2 text-xs bg-white/5 text-white/20 border border-white/5 cursor-not-allowed line-through'
                : 'rounded-lg px-2 py-2 text-xs bg-white/5 text-white/80 border border-white/10 hover:border-sky-400/40 hover:text-white transition';

            if (!isTaken) {
                btn.addEventListener('click', () => {
                    slotsBox.querySelectorAll('button').forEach(b => {
                        b.classList.remove('bg-gradient-to-r', 'from-sky-400', 'to-indigo-500', 'text-black', 'font-semibold');
                    });
                    btn.classList.add('bg-gradient-to-r', 'from-sky-400', 'to-indigo-500', 'text-black', 'font-semibold');
                    timeInput.value = time;
                    form.classList.remove('hidden');
                });
            }

            slotsBox.appendChild(btn);
        });
    }

    function renderList() {
        listBox.querySelectorAll('.iarecep-cal-item').forEach(el => el.remove());

        if (bookedAppointments.length === 0) {
            listEmpty.classList.remove('hidden');
            return;
        }
        listEmpty.classList.add('hidden');

        [...bookedAppointments]
            .sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time))
            .forEach(a => {
                const [y, m, d] = a.date.split('-');
                const item = document.createElement('div');
                item.className = 'iarecep-cal-item flex items-center justify-between rounded-lg bg-white/5 border border-white/10 px-4 py-2.5 text-sm';
                item.innerHTML = `
                    <span class="text-white/80">${d}/${m}/${y} à ${a.time}</span>
                    <span class="text-white/40 text-xs">${a.full_name}</span>
                `;
                listBox.appendChild(item);
            });
    }

    async function loadAppointments() {
        try {
            const res = await fetch(`${window.IARECEP.routes.appointmentsIndex}?token=${encodeURIComponent(window.IARECEP.token)}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (res.ok) {
                bookedAppointments = data.appointments || [];
                renderCalendar();
                renderList();
                if (selectedDate) selectDate(selectedDate);
            }
        } catch (e) {
            // silencieux, le calendrier reste utilisable sans la liste
        }
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

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        errorEl.classList.add('hidden');

        const fd = new FormData(form);
        fd.append('token', window.IARECEP.token);
        fd.append('date', selectedDate);

        try {
            const res = await fetch(window.IARECEP.routes.appointmentsStore, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.IARECEP.csrf, 'Accept': 'application/json' },
                body: fd,
            });
            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.error || "Une erreur est survenue.";
                errorEl.classList.remove('hidden');
                return;
            }

            bookedAppointments.push(data.appointment);
            renderCalendar();
            renderList();
            form.reset();
            form.classList.add('hidden');
            bookingBox.classList.add('hidden');
            emptyState.classList.remove('hidden');
            selectedDate = null;
            renderCalendar();
        } catch (e) {
            errorEl.textContent = "Connexion impossible. Réessayez.";
            errorEl.classList.remove('hidden');
        }
    });

    renderCalendar();

    // Charge les rendez-vous dès que le calendrier devient visible.
    window.addEventListener('iarecep:started', loadAppointments);
    if (window.IARECEP.existingMessages && window.IARECEP.existingMessages.length) {
        loadAppointments();
    }

    // API exposée aux scripts du chat (texte/vocal).
    window.IARECEP.calendar = {
        // Ajout optimiste immédiat (affichage instantané sans attendre le réseau)
        addAppointment(appointment) {
            if (!appointment) return;
            const exists = bookedAppointments.some(a => a.date === appointment.date && a.time === appointment.time);
            if (!exists) bookedAppointments.push(appointment);
            renderCalendar();
            renderList();
            if (selectedDate === appointment.date) selectDate(selectedDate);
        },
        // Re-synchronisation forcée depuis le serveur : garantit la cohérence
        // avec la base de données, quel que soit l'état local.
        refresh() {
            return loadAppointments();
        },
    };
})();
</script>