{{-- resources/views/partials/iarecep-text.blade.php --}}
<div class="text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-4 sm:p-6">
    <div id="iarecep-text-messages" class="space-y-3 max-h-96 overflow-y-auto pr-1 mb-4"></div>

    <form id="iarecep-text-form" class="flex items-center gap-2">
        <input type="text" id="iarecep-text-input" placeholder="Écrivez votre message..." autocomplete="off"
            class="flex-1 bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
        <button type="submit"
            class="shrink-0 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-4 py-3 text-black hover:shadow-[0_0_20px_rgba(56,189,248,0.4)] transition-shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
        </button>
    </form>
    <p id="iarecep-text-error" class="hidden text-xs text-red-400 mt-2"></p>
</div>

<script>
(function () {
    const list = document.getElementById('iarecep-text-messages');
    const form = document.getElementById('iarecep-text-form');
    const input = document.getElementById('iarecep-text-input');
    const errorEl = document.getElementById('iarecep-text-error');

    function addBubble(role, content) {
        const wrap = document.createElement('div');
        wrap.className = role === 'assistant'
            ? 'flex justify-start'
            : 'flex justify-end';

        const bubble = document.createElement('div');
        bubble.className = role === 'assistant'
            ? 'max-w-[85%] rounded-xl rounded-bl-sm bg-white/5 border border-white/10 px-4 py-2.5 text-sm text-white/90'
            : 'max-w-[85%] rounded-xl rounded-br-sm bg-gradient-to-r from-sky-400/20 to-indigo-500/20 border border-sky-400/30 px-4 py-2.5 text-sm text-white';
        bubble.textContent = content;

        wrap.appendChild(bubble);
        list.appendChild(wrap);
        list.scrollTop = list.scrollHeight;
    }

    async function sendMessage(message) {
    addBubble('user', message);
    errorEl.classList.add('hidden');

    try {
        const res = await fetch(window.IARECEP.routes.chat, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.IARECEP.csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ token: window.IARECEP.token, message }),
        });
        const data = await res.json();

        if (!res.ok) {
            errorEl.textContent = data.error || "Une erreur est survenue.";
            errorEl.classList.remove('hidden');
            return;
        }

        addBubble('assistant', data.reply);

        // Mise à jour optimiste immédiate...
        if (data.appointment) {
            window.IARECEP.calendar?.addAppointment(data.appointment);
        }
        // ...puis resynchro serveur systématique pour garantir la cohérence
        // avec la base (corrige les cas où l'IA réserve sans que le champ
        // "appointment" soit correctement remonté au premier essai).
        window.IARECEP.calendar?.refresh();

        window.dispatchEvent(new CustomEvent('iarecep:reply', { detail: { reply: data.reply, appointment: data.appointment } }));
    } catch (e) {
        errorEl.textContent = "Connexion impossible. Réessayez.";
        errorEl.classList.remove('hidden');
    }
}

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const value = input.value.trim();
        if (!value) return;
        input.value = '';
        sendMessage(value);
    });

    window.addEventListener('iarecep:started', function (e) {
        if (e.detail.mode !== 'vocal') addBubble('assistant', e.detail.welcome);
    });

    // Reprise de conversation existante (rechargement de page).
    if (window.IARECEP.existingMessages && window.IARECEP.existingMessages.length) {
        document.getElementById('iarecep-form-wrapper').classList.add('hidden');
        document.getElementById('iarecep-text-wrapper').classList.remove('hidden');
        document.getElementById('iarecep-satisfaction-wrapper').classList.remove('hidden');
        window.IARECEP.existingMessages.forEach(m => addBubble(m.role, m.content));
    }
})();
</script>