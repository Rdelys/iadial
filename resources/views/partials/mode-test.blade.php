{{-- ================= MODE TEST ================= --}}
<section id="mode-test" class="reveal max-w-7xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24">
    <div class="rounded-2xl sm:rounded-3xl border border-white/10 bg-gradient-to-b from-white/[0.04] to-transparent p-5 sm:p-8 lg:p-12 grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">

        {{-- Colonne texte + suggestions --}}
        <div>
            <h2 class="font-display text-xl sm:text-2xl font-semibold flex items-center gap-2">
                <span class="text-sky-400">✦</span> Mode test
            </h2>
            <p class="mt-4 text-white/50 text-sm sm:text-base leading-relaxed">
                Discutez en direct avec Léa, la réceptionniste IA d'IA DIAL. Posez-lui vos questions sur
                notre service et découvrez comment fonctionnerait votre propre assistant.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <button class="rounded-lg bg-sky-400/15 border border-sky-400/30 text-sky-300 text-sm font-medium px-4 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8-1.06 0-2.077-.163-3.02-.465L3 21l1.535-3.905C3.56 15.897 3 14.482 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Chat (texte)
                </button>
                <button
                    x-data
                    @click="$dispatch('fill-chat', { message: '📞 Puis-je tester la réceptionniste vocale ?' })"
                    class="rounded-lg border border-white/10 text-white/50 text-sm font-medium px-4 py-2.5 flex items-center gap-2 hover:bg-white/5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Appel vocal (démo)
                </button>
            </div>

            <div class="mt-5 flex flex-col gap-2.5 sm:gap-3" x-data="{ fill(q) { $dispatch('fill-chat', { message: q }) } }">
                @foreach ([
                    'Quels sont vos horaires d’ouverture ?',
                    'Combien de temps pour mettre en place mon assistant ?',
                    'Quels services proposez-vous ?',
                    'Je souhaite être recontacté(e)',
                ] as $question)
                    <button @click="fill('{{ $question }}')"
                        class="text-left text-sm text-white/70 border border-white/10 rounded-lg px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-white/5 hover:border-white/20 transition">
                        <span>{{ $question }}</span>
                        <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
                    </button>
                @endforeach
            </div>

            <p class="mt-6 text-xs text-white/30 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Ceci est un mode test. Aucune donnée personnelle n'est enregistrée durablement.
            </p>
        </div>

        {{-- Fenêtre de chat --}}
        <div x-data="{
                messages: [
                    { from: 'bot', text: 'Bonjour ! 👋 Je suis Léa, la réceptionniste IA d’IA DIAL. Posez-moi vos questions, ou testez ce que vivrait un de vos futurs clients !' }
                ],
                draft: '',
                loading: false,
                send(text) {
                    const msg = (text ?? this.draft).trim();
                    if (!msg || this.loading) return;

                    this.messages.push({ from: 'user', text: msg });
                    this.draft = '';
                    this.loading = true;
                    this.scrollDown();

                    fetch('{{ route('iarecep.demo.chat') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ message: msg }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.messages.push({ from: 'bot', text: data.reply ?? 'Désolée, je n’ai pas compris.' });
                    })
                    .catch(() => {
                        this.messages.push({ from: 'bot', text: 'Désolée, un souci technique momentané. Réessayez ?' });
                    })
                    .finally(() => {
                        this.loading = false;
                        this.scrollDown();
                    });
                },
                scrollDown() {
                    this.$nextTick(() => this.$refs.scrollArea.scrollTop = this.$refs.scrollArea.scrollHeight);
                }
            }"
            @fill-chat.window="send($event.detail.message)"
            class="card-hover rounded-2xl border border-white/10 bg-[#0a0a0c] overflow-hidden shadow-2xl">

            <div class="bg-gradient-to-r from-sky-500 to-blue-600 px-4 sm:px-5 py-3.5 sm:py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="IA DIAL" class="h-8 w-8 rounded-full bg-white/20 p-1">
                    <div>
                        <p class="text-sm font-semibold text-white">Léa · Réceptionniste IA DIAL</p>
                        <p class="text-xs text-white/80 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> En ligne · 8h-18h
                        </p>
                    </div>
                </div>
            </div>

            <div x-ref="scrollArea" class="h-72 sm:h-80 overflow-y-auto px-4 sm:px-5 py-5 space-y-4 scroll-smooth">
                <template x-for="(m, i) in messages" :key="i">
                    <div :class="m.from === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="m.from === 'user'
                                ? 'bg-gradient-to-r from-sky-400 to-indigo-500 text-black rounded-2xl rounded-tr-sm'
                                : 'bg-white/5 border border-white/10 text-white/80 rounded-2xl rounded-tl-sm'"
                            class="max-w-[85%] sm:max-w-[80%] px-4 py-2.5 text-sm leading-relaxed" x-text="m.text"></div>
                    </div>
                </template>

                <div x-show="loading" x-cloak class="flex justify-start">
                    <div class="bg-white/5 border border-white/10 text-white/40 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm">
                        Léa est en train d'écrire…
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 p-2.5 sm:p-3 flex items-center gap-2">
                <input x-model="draft" @keydown.enter="send()" type="text" placeholder="Écrivez votre message..."
                    :disabled="loading"
                    class="flex-1 min-w-0 bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 disabled:opacity-50">
                <button @click="send()" :disabled="loading" class="w-10 h-10 shrink-0 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 flex items-center justify-center text-black disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>