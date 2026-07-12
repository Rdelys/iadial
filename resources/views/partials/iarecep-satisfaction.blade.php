{{-- resources/views/partials/iarecep-satisfaction.blade.php --}}
<div class="text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-6 sm:p-8">
    <h3 class="text-lg font-semibold text-white mb-1">Satisfait de la démo ?</h3>
    <p class="text-sm text-white/50 mb-5">Laissez-nous vos coordonnées, notre équipe vous recontacte sous 24h pour activer votre réceptionniste IA.</p>

    <form id="iarecep-satisfaction-form" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-white/60 mb-2">Téléphone (optionnel)</label>
            <input type="tel" name="phone" placeholder="+261 34 00 000 00"
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
        </div>
        <div>
            <label class="block text-sm text-white/60 mb-2">Message (optionnel)</label>
            <textarea name="message" rows="3" placeholder="Précisez vos besoins ou vos disponibilités..."
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition resize-none"></textarea>
        </div>
        <button type="submit"
            class="w-full rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-6 py-3 text-sm font-semibold text-black hover:shadow-[0_0_20px_rgba(52,226,192,0.4)] transition-shadow">
            Être contacté par un conseiller
        </button>
        <p id="iarecep-satisfaction-msg" class="hidden text-xs text-center"></p>
    </form>
</div>

<script>
document.getElementById('iarecep-satisfaction-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const msgEl = document.getElementById('iarecep-satisfaction-msg');
    const fd = new FormData(this);
    fd.append('token', window.IARECEP.token);

    try {
        const res = await fetch(window.IARECEP.routes.demande, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.IARECEP.csrf, 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();

        msgEl.textContent = res.ok ? data.message : (data.error || "Une erreur est survenue.");
        msgEl.className = res.ok ? 'text-xs text-center text-emerald-400' : 'text-xs text-center text-red-400';
        msgEl.classList.remove('hidden');
        if (res.ok) this.reset();
    } catch {
        msgEl.textContent = "Connexion impossible. Réessayez.";
        msgEl.className = 'text-xs text-center text-red-400';
        msgEl.classList.remove('hidden');
    }
});
</script>