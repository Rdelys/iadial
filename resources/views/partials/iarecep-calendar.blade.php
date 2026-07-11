{{-- resources/views/partials/iarecep-calendar.blade.php --}}
<div class="text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-6 sm:p-8">
    <h3 class="text-lg font-semibold text-white mb-1">📅 Prendre rendez-vous</h3>
    <p class="text-sm text-white/50 mb-5">Choisissez une date pour tester la prise de rendez-vous automatique.</p>

    {{-- En-tête calendrier --}}
    <div class="flex items-center justify-between mb-4">
        <button type="button" id="iarecep-cal-prev" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-white/60 hover:text-white hover:border-sky-400/40 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <span id="iarecep-cal-label" class="text-sm font-medium text-white"></span>
        <button type="button" id="iarecep-cal-next" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-white/60 hover:text-white hover:border-sky-400/40 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>

    {{-- Grille des jours --}}
    <div class="grid grid-cols-7 gap-1 text-center text-xs text-white/40 mb-2">
        <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
    </div>
    <div id="iarecep-cal-grid" class="grid grid-cols-7 gap-1 mb-6"></div>

    {{-- Sélecteur d'heure + formulaire (apparaît après clic sur une date) --}}
    <div id="iarecep-cal-booking" class="hidden border-t border-white/10 pt-5">
        <p class="text-sm text-white/70 mb-3">
            Créneau pour le <span id="iarecep-cal-selected-date" class="text-sky-300 font-medium"></span>
        </p>

        <div id="iarecep-cal-slots" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mb-5"></div>

        <form id="iarecep-cal-form" class="hidden space-y-4">
            @csrf
            <input type="hidden" id="iarecep-cal-time-input" name="time">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-white/60 mb-2">Nom complet</label>
                    <input type="text" name="full_name" required placeholder="Jean Dupont"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-2">Téléphone (optionnel)</label>
                    <input type="tel" name="phone" placeholder="+261 34 00 000 00"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
                </div>
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2">Motif (optionnel)</label>
                <input type="text" name="notes" placeholder="Ex : détartrage, contrôle..."
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
            </div>
            <button type="submit"
                class="w-full rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-6 py-3 text-sm font-semibold text-black hover:shadow-[0_0_20px_rgba(56,189,248,0.4)] transition-shadow">
                Confirmer le rendez-vous
            </button>
            <p id="iarecep-cal-error" class="hidden text-xs text-red-400 text-center"></p>
        </form>
    </div>

    {{-- Liste des rendez-vous déjà pris --}}
    <div class="border-t border-white/10 pt-5 mt-6">
        <h4 class="text-sm font-medium text-white/70 mb-3">Rendez-vous réservés</h4>
        <div id="iarecep-cal-list" class="space-y-2">
            <p id="iarecep-cal-list-empty" class="text-xs text-white/30">Aucun rendez-vous pour le moment.</p>
        </div>
    </div>
</div>

<script>
(function () {
    const grid = document.getElementById('iarecep-cal-grid');
    const label = document.getElementById('iarecep-cal-label');
    const prevBtn = document.getElementById('iarecep-cal-prev');
    const nextBtn = document.getElementById('iarecep-cal-next');
    const bookingBox = document.getElementById('iarecep-cal-booking');
    const selectedDateEl = document.getElementById('iarecep-cal-selected-date');
    const slotsBox = document.getElementById('iarecep-cal-slots');
    const form = document.getElementById('iarecep-cal-form');
    const timeInput = document.getElementById('iarecep-cal-time-input');
    const errorEl = document.getElementById('iarecep-cal-error');
    const listBox = document.getElementById('iarecep-cal-list');
    const listEmpty = document.getElementById('iarecep-cal-list-empty');

    const MONTHS_FR = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const SLOTS = ['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00'];

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

    function bookedTimesFor(dateStr) {
        return bookedAppointments.filter(a => a.date === dateStr).map(a => a.time);
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

    for (let i = 0; i < startOffset; i++) {
        const empty = document.createElement('div');
        grid.appendChild(empty);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const cellDate = new Date(year, month, day);
        const dateStr = fmtDate(cellDate);
        const isPast = cellDate < today;

        const takenTimes = bookedTimesFor(dateStr);
        const isFullyBooked = takenTimes.length >= SLOTS.length;
        const isPartiallyBooked = takenTimes.length > 0 && !isFullyBooked;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = day;
        btn.disabled = isPast || isFullyBooked;

        let cls = 'aspect-square flex items-center justify-center rounded-lg text-sm transition relative ';

        if (isPast) {
            cls += 'text-white/20 cursor-not-allowed';
        } else if (selectedDate === dateStr) {
            cls += 'bg-gradient-to-r from-sky-400 to-indigo-500 text-black font-semibold';
        } else if (isFullyBooked) {
            // Toutes les heures sont prises → rouge
            cls += 'bg-red-500/20 border border-red-400/50 text-red-300 cursor-not-allowed font-semibold';
        } else if (isPartiallyBooked) {
            // Au moins un créneau pris sur cette date → bleu
            cls += 'bg-sky-500/20 border border-sky-400/50 text-sky-300 hover:border-sky-400/80 cursor-pointer font-medium';
        } else {
            cls += 'text-white/80 bg-white/5 border border-white/10 hover:border-sky-400/40 hover:text-white cursor-pointer';
        }

        btn.className = cls;
        btn.title = isFullyBooked
            ? 'Toutes les heures sont réservées'
            : (isPartiallyBooked ? `${takenTimes.length} créneau(x) déjà réservé(s)` : '');

        if (!isPast && !isFullyBooked) {
            btn.addEventListener('click', () => selectDate(dateStr));
        }

        grid.appendChild(btn);
    }
}

    function selectDate(dateStr) {
        selectedDate = dateStr;
        renderCalendar();

        const [y, m, d] = dateStr.split('-');
        selectedDateEl.textContent = `${d}/${m}/${y}`;
        bookingBox.classList.remove('hidden');
        form.classList.add('hidden');
        errorEl.classList.add('hidden');

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
        },
        // Re-synchronisation forcée depuis le serveur : garantit la cohérence
        // avec la base de données, quel que soit l'état local.
        refresh() {
            return loadAppointments();
        },
    };
})();
</script>