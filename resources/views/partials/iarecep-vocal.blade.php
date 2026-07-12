{{-- resources/views/partials/iarecep-vocal.blade.php --}}
<div class="text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-6 text-center">
    <div id="iarecep-vocal-log" class="space-y-2 max-h-80 overflow-y-auto text-left mb-6 text-sm text-white/70"></div>

    <button type="button" id="iarecep-vocal-btn"
        class="mx-auto flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 text-black shadow-[0_0_25px_rgba(52,226,192,0.4)] transition-transform active:scale-95">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v3m-4 0h8M12 15a3 3 0 003-3V6a3 3 0 10-6 0v6a3 3 0 003 3zm7-3a7 7 0 01-14 0"/>
        </svg>
    </button>
    <p id="iarecep-vocal-status" class="text-xs text-white/40 mt-3">Appuyez pour parler</p>
    <p id="iarecep-vocal-error" class="hidden text-xs text-red-400 mt-2"></p>
</div>

<script>
(function () {
    const logEl = document.getElementById('iarecep-vocal-log');
    const btn = document.getElementById('iarecep-vocal-btn');
    const statusEl = document.getElementById('iarecep-vocal-status');
    const errorEl = document.getElementById('iarecep-vocal-error');

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognizing = false;
    let recognition = null;

    if (!SpeechRecognition) {
        errorEl.textContent = "La reconnaissance vocale n'est pas supportée par ce navigateur. Utilisez le mode texte.";
        errorEl.classList.remove('hidden');
        btn.disabled = true;
    } else {
        recognition = new SpeechRecognition();
        recognition.lang = 'fr-FR';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
    }

    function log(role, text) {
        const p = document.createElement('p');
        p.innerHTML = `<span class="${role === 'assistant' ? 'text-sky-300' : 'text-white'} font-medium">${role === 'assistant' ? 'IA' : 'Vous'} :</span> ${text}`;
        logEl.appendChild(p);
        logEl.scrollTop = logEl.scrollHeight;
    }

    function speak(text) {
        if (!window.speechSynthesis) return;
        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = 'fr-FR';
        window.speechSynthesis.speak(utter);
    }

    async function sendMessage(message) {
    log('user', message);
    statusEl.textContent = "La réceptionniste réfléchit...";

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
            statusEl.textContent = "Appuyez pour parler";
            return;
        }

        log('assistant', data.reply);
        speak(data.reply);
        statusEl.textContent = "Appuyez pour parler";

        if (data.appointment) {
            window.IARECEP.calendar?.addAppointment(data.appointment);
        }
        window.IARECEP.calendar?.refresh();
    } catch (e) {
        errorEl.textContent = "Connexion impossible. Réessayez.";
        errorEl.classList.remove('hidden');
        statusEl.textContent = "Appuyez pour parler";
    }
}

    if (recognition) {
        btn.addEventListener('click', () => {
            if (recognizing) return;
            recognizing = true;
            statusEl.textContent = "Je vous écoute...";
            recognition.start();
        });

        recognition.addEventListener('result', (e) => {
            const transcript = e.results[0][0].transcript;
            sendMessage(transcript);
        });

        recognition.addEventListener('end', () => { recognizing = false; });
        recognition.addEventListener('error', () => {
            recognizing = false;
            statusEl.textContent = "Appuyez pour parler";
        });
    }

    window.addEventListener('iarecep:started', function (e) {
        if (e.detail.mode === 'vocal') {
            log('assistant', e.detail.welcome);
            speak(e.detail.welcome);
        }
    });
})();
</script>