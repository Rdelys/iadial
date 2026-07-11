<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "IADial - Votre réceptionniste IA, toujours à l'écoute")</title>
    <meta name="description" content="IADial répond à vos clients par téléphone et par chat, 24h/24, grâce à l'intelligence artificielle.">
    <link rel="icon" href="{{ asset('logo.png') }}">

    {{-- Polices --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['"Space Grotesk"', 'sans-serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        sky: { 400: '#38bdf8' },
                        indigo: { 500: '#6366f1' },
                    },
                },
            },
        }
    </script>

    {{-- Alpine.js pour le menu mobile et le chat interactif --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #030304;
        }
        .font-display { font-family: 'Space Grotesk', sans-serif; }

        /* Grille de points façon réseau neuronal, en écho au logo */
        .neural-grid {
            background-image: radial-gradient(rgba(56, 189, 248, 0.15) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: radial-gradient(ellipse 60% 50% at 50% 0%, black 40%, transparent 100%);
        }

        /* Halo lumineux derrière le contenu */
        .glow-orb {
            background: radial-gradient(circle, rgba(56, 189, 248, 0.35) 0%, rgba(37, 99, 235, 0.12) 40%, transparent 70%);
            filter: blur(60px);
        }

        ::selection { background: #38bdf8; color: #000; }

        /* ===== ANIMATIONS AU SCROLL ===== */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            transition-delay: var(--reveal-delay, 0ms);
        }
        .reveal.in-view {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger automatique pour les grilles de cartes */
        .reveal-group > *:nth-child(1) { --reveal-delay: 0ms; }
        .reveal-group > *:nth-child(2) { --reveal-delay: 90ms; }
        .reveal-group > *:nth-child(3) { --reveal-delay: 180ms; }
        .reveal-group > *:nth-child(4) { --reveal-delay: 270ms; }
        .reveal-group > *:nth-child(5) { --reveal-delay: 360ms; }

        /* ===== ANIMATION AU CHARGEMENT (hero) ===== */
        @keyframes heroIn {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .hero-in-1 { animation: heroIn 0.7s cubic-bezier(0.16,1,0.3,1) 0.05s both; }
        .hero-in-2 { animation: heroIn 0.7s cubic-bezier(0.16,1,0.3,1) 0.15s both; }
        .hero-in-3 { animation: heroIn 0.7s cubic-bezier(0.16,1,0.3,1) 0.25s both; }
        .hero-in-4 { animation: heroIn 0.7s cubic-bezier(0.16,1,0.3,1) 0.35s both; }
        .hero-in-5 { animation: heroIn 0.7s cubic-bezier(0.16,1,0.3,1) 0.45s both; }

        /* ===== BOUTONS : effet de balayage lumineux ===== */
        .btn-shine {
            position: relative;
            overflow: hidden;
        }
        .btn-shine::after {
            content: '';
            position: absolute;
            top: 0; left: -75%;
            width: 50%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.35), transparent);
            transform: skewX(-20deg);
            transition: left 0.65s ease;
        }
        .btn-shine:hover::after { left: 125%; }

        /* ===== TEXTE GRADIENT ANIMÉ ===== */
        .text-gradient-animated {
            background-size: 200% auto;
            animation: gradientShift 6s ease-in-out infinite;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% center; }
            50% { background-position: 100% center; }
        }

        /* ===== CARTES : lift au survol ===== */
        .card-hover {
            transition: transform 0.35s cubic-bezier(0.16,1,0.3,1), border-color 0.35s ease, box-shadow 0.35s ease;
        }
        .card-hover:hover {
            transform: translateY(-6px);
            border-color: rgba(56, 189, 248, 0.35);
            box-shadow: 0 20px 40px -20px rgba(56, 189, 248, 0.25);
        }

        /* ===== Flottement des cartes hero ===== */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal, .hero-in-1, .hero-in-2, .hero-in-3, .hero-in-4, .hero-in-5,
            .btn-shine::after, .text-gradient-animated, .card-hover {
                animation: none !important;
                transition: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#030304] text-white antialiased relative overflow-x-hidden">

    {{-- Décor de fond fixe --}}
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-0 neural-grid opacity-60"></div>
        <div class="glow-orb absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[700px]"></div>
    </div>

    {{-- NAVBAR --}}
    <header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-white/10 bg-black/60 backdrop-blur-xl">
        <nav class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="relative">
                    <div class="absolute inset-0 rounded-full bg-sky-400/40 blur-md group-hover:bg-sky-400/60 transition"></div>
                    <img src="{{ asset('logo.png') }}" alt="IADial" class="relative h-9 w-auto">
                </div>
                <span class="font-display text-lg font-semibold tracking-tight">
                    IA<span class="text-sky-400">Dial</span>
                </span>
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm text-white/60">
                <a href="#fonctionnalites" class="relative hover:text-white transition py-1
                    after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-sky-400
                    hover:after:w-full after:transition-all">Fonctionnalités</a>
                <a href="#tarifs" class="relative hover:text-white transition py-1
                    after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-sky-400
                    hover:after:w-full after:transition-all">Tarifs</a>
                <a href="#demo" class="relative hover:text-white transition py-1
                    after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-sky-400
                    hover:after:w-full after:transition-all">Démo</a>
                <a href="#faq" class="relative hover:text-white transition py-1
                    after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-sky-400
                    hover:after:w-full after:transition-all">FAQ</a>
                <a href="#contact" class="relative hover:text-white transition py-1
                    after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-sky-400
                    hover:after:w-full after:transition-all">Contact</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('iarecep') }}" target="_blank" rel="noopener"
                   class="btn-shine hidden sm:inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                       px-5 py-2.5 text-sm font-medium text-black shadow-[0_0_20px_rgba(56,189,248,0.35)]
                       hover:shadow-[0_0_30px_rgba(56,189,248,0.55)] transition-shadow">
                    Essayer gratuitement
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
                </a>

                {{-- Bouton menu mobile --}}
                <button @click="open = !open" class="md:hidden p-2 text-white/70 hover:text-white">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </nav>

        {{-- Menu mobile --}}
        <div x-show="open" x-cloak x-transition class="md:hidden border-t border-white/10 bg-black/95 px-6 py-4 space-y-3 text-sm text-white/70">
            <a href="#fonctionnalites" class="block hover:text-white">Fonctionnalités</a>
            <a href="#tarifs" class="block hover:text-white">Tarifs</a>
            <a href="#demo" class="block hover:text-white">Démo</a>
            <a href="#faq" class="block hover:text-white">FAQ</a>
            <a href="#contact" class="block hover:text-white">Contact</a>
            <a href="{{ route('iarecep') }}" target="_blank" rel="noopener"
               class="btn-shine block text-center rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-5 py-2.5 font-medium text-black mt-2">
                Essayer gratuitement
            </a>
        </div>
    </header>

    {{-- CONTENU --}}
    <main class="relative">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="relative mt-32 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6 py-16 grid grid-cols-2 md:grid-cols-5 gap-10">
            <div class="col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('logo.png') }}" alt="IADial" class="h-8 w-auto">
                    <span class="font-display font-semibold text-lg">IA<span class="text-sky-400">Dial</span></span>
                </div>
                <p class="text-white/40 text-sm leading-relaxed max-w-xs">
                    Le réceptionniste IA qui répond à vos clients par téléphone et par chat, 24h/24 et 7j/7.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white mb-4">Produit</h4>
                <ul class="space-y-2 text-sm text-white/50">
                    <li><a href="#fonctionnalites" class="hover:text-white transition">Fonctionnalités</a></li>
                    <li><a href="#tarifs" class="hover:text-white transition">Tarifs</a></li>
                    <li><a href="#demo" class="hover:text-white transition">Démo</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white mb-4">Ressources</h4>
                <ul class="space-y-2 text-sm text-white/50">
                    <li><a href="#faq" class="hover:text-white transition">FAQ</a></li>
                    <li><a href="#contact" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white mb-4">Légal</h4>
                <ul class="space-y-2 text-sm text-white/50">
                    <li><a href="#" class="hover:text-white transition">Mentions légales</a></li>
                    <li><a href="#" class="hover:text-white transition">Confidentialité</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-white/30 text-xs">&copy; {{ date('Y') }} IADial. Tous droits réservés.</p>
                <p class="text-white/30 text-xs">Conforme RGPD &middot; Hébergé en Europe</p>
            </div>
        </div>
    </footer>

    {{-- Observer pour les animations au scroll --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });
    </script>

</body>
</html>