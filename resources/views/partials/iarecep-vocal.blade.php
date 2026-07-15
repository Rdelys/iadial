{{-- resources/views/partials/iarecep-vocal.blade.php --}}
<div class="text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-6 text-center">
    <div id="iarecep-vocal-log" class="space-y-2 max-h-80 overflow-y-auto text-left mb-6 text-sm text-white/70"></div>

    <button type="button" id="iarecep-vocal-btn"
        class="mx-auto flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 text-black shadow-[0_0_25px_rgba(52,226,192,0.4)] transition-transform active:scale-95">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v3m-4 0h8M12 15a3 3 0 003-3V6a3 3 0 10-6 0v6a3 3 0 003 3zm7-3a7 7 0 01-14 0"/>
        </svg>
    </button>
    <p id="iarecep-vocal-status" class="text-xs text-white/40 mt-3">Appuyez pour appeler</p>
    <p id="iarecep-vocal-error" class="hidden text-xs text-red-400 mt-2"></p>
</div>

<script src="https://unpkg.com/@vapi-ai/web@latest/dist/vapi.js"></script>
<script>
(function () {
    const logEl = document.getElementById('iarecep-vocal-log');
    const btn = document.getElementById('iarecep-vocal-btn');
    const statusEl = document.getElementById('iarecep-vocal-status');
    const errorEl = document.getElementById('iarecep-vocal-error');

    let vapi = null;
    let onCall = false;
    let starting = false;

    function log(role, text) {
        if (!text) return;
        const p = document.createElement('p');
        p.innerHTML = `<span class="${role === 'assistant' ? 'text-sky-300' : 'text-white'} font-medium">${role === 'assistant' ? 'IA' : 'Vous'} :</span> ${text}`;
        logEl.appendChild(p);
        logEl.scrollTop = logEl.scrollHeight;
    }

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.classList.remove('hidden');
    }

    function setUiCalling() {
        statusEl.textContent = "Raccrocher";
        btn.classList.add('ring-4', 'ring-red-400/50');
    }

    function setUiIdle() {
        statusEl.textContent = "Appuyez pour appeler";
        btn.classList.remove('ring-4', 'ring-red-400/50');
    }

    async function fetchAssistantConfig() {
        const res = await fetch(window.IARECEP.routes.vapiConfig, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.IARECEP.csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ token: window.IARECEP.token }),
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.error || "Impossible de préparer l'appel.");
        }
        return data;
    }

    async function startCall() {
        if (onCall || starting) return;
        starting = true;
        errorEl.classList.add('hidden');
        statusEl.textContent = "Connexion en cours...";

        try {
            if (!window.Vapi) {
                throw new Error("Le module vocal n'a pas pu se charger. Utilisez le mode texte.");
            }

            const { publicKey, assistant } = await fetchAssistantConfig();

            if (!vapi) {
                vapi = new window.Vapi(publicKey);

                vapi.on('call-start', () => {
                    onCall = true;
                    starting = false;
                    setUiCalling();
                });

                vapi.on('call-end', () => {
                    onCall = false;
                    starting = false;
                    setUiIdle();
                });

                vapi.on('message', (message) => {
                    if (message.type === 'transcript' && message.transcriptType === 'final') {
                        log(message.role, message.transcript);
                    }
                    if (message.type === 'tool-calls' || message.type === 'tool-calls-result') {
                        // Un rendez-vous a potentiellement été (dés)réservé côté serveur.
                        window.IARECEP.calendar?.refresh();
                    }
                });

                vapi.on('error', (e) => {
                    console.error(e);
                    starting = false;
                    onCall = false;
                    setUiIdle();
                    showError("La communication vocale a été interrompue. Réessayez.");
                });
            }

            await vapi.start(assistant);
        } catch (e) {
            starting = false;
            setUiIdle();
            showError(e.message || "Connexion impossible. Réessayez.");
        }
    }

    function endCall() {
        vapi?.stop();
    }

    btn.addEventListener('click', () => {
        if (onCall) {
            endCall();
        } else {
            startCall();
        }
    });

    window.addEventListener('iarecep:started', function (e) {
        if (e.detail.mode === 'vocal') {
            log('assistant', e.detail.welcome);
        }
    });
})();
</script>