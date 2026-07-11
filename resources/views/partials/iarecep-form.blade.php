{{-- resources/views/partials/iarecep-form.blade.php --}}
<form id="iarecep-form"
    class="mt-2 text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-6 sm:p-8 space-y-5">
    @csrf
    <input type="hidden" id="iarecep-hidden-mode" name="mode" value="text">

    <div>
        <label class="block text-sm text-white/60 mb-2">Nom de l'entreprise</label>
        <input type="text" name="company_name" required placeholder="Ex : Cabinet Dupont"
            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm text-white/60 mb-2">Votre nom</label>
            <input type="text" name="full_name" required placeholder="Jean Dupont"
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
        </div>
        <div>
            <label class="block text-sm text-white/60 mb-2">Email professionnel</label>
            <input type="email" name="email" required placeholder="jean@entreprise.com"
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
        </div>
    </div>

    <div>
        <label class="block text-sm text-white/60 mb-2">Secteur d'activité</label>
        <select name="sector"
            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-sky-400/50 transition">
            <option class="bg-[#0a0a0c]">Cabinet médical / dentaire</option>
            <option class="bg-[#0a0a0c]">Salon de beauté / coiffure</option>
            <option class="bg-[#0a0a0c]">Immobilier</option>
            <option class="bg-[#0a0a0c]">Restauration</option>
            <option class="bg-[#0a0a0c]">Autre</option>
        </select>
    </div>

    <div>
        <label class="block text-sm text-white/60 mb-2">À propos de votre entreprise</label>
        <textarea name="about" required rows="4" placeholder="Décrivez votre activité, vos horaires, vos services, comment la réceptionniste doit accueillir vos clients..."
            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition resize-none"></textarea>
        <p class="text-xs text-white/30 mt-1">Ces informations servent uniquement à personnaliser votre essai gratuit.</p>
    </div>

    <button type="submit" id="iarecep-submit-btn"
        class="btn-shine w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
            px-6 py-3.5 text-sm font-semibold text-black shadow-[0_0_20px_rgba(56,189,248,0.35)]
            hover:shadow-[0_0_30px_rgba(56,189,248,0.55)] transition-shadow disabled:opacity-60">
        Créer mon réceptionniste IA
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
    </button>

    <p id="iarecep-form-error" class="hidden text-xs text-red-400 text-center"></p>

    <p class="text-xs text-white/30 text-center flex items-center justify-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        Vos données sont chiffrées et hébergées en Europe.
    </p>
</form>

<script>
document.getElementById('iarecep-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('iarecep-submit-btn');
    const errorEl = document.getElementById('iarecep-form-error');
    errorEl.classList.add('hidden');
    btn.disabled = true;

    const fd = new FormData(this);

    try {
        const res = await fetch(window.IARECEP.routes.store, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.IARECEP.csrf, 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || "Une erreur est survenue.");
        }

        // Le formulaire disparaît, place à la réceptionniste IA.
        document.getElementById('iarecep-form-wrapper').classList.add('hidden');

        const mode = window.IARECEP.getMode();
        const targetWrapper = mode === 'vocal' ? 'iarecep-vocal-wrapper' : 'iarecep-text-wrapper';
        document.getElementById(targetWrapper).classList.remove('hidden');
        document.getElementById('iarecep-satisfaction-wrapper').classList.remove('hidden');
document.getElementById('iarecep-satisfaction-wrapper').classList.remove('hidden');
document.getElementById('iarecep-calendar-wrapper').classList.remove('hidden'); // ← ajouter cette ligne

window.dispatchEvent(new CustomEvent('iarecep:started', { detail: { welcome: data.welcome, mode } }));
    } catch (err) {
        errorEl.textContent = err.message;
        errorEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
    }
});
</script>