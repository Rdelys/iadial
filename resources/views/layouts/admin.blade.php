<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin') — IA DIAL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @stack('head')
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, .brand { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-white flex">
    {{-- Sidebar --}}
    <aside class="w-64 shrink-0 border-r border-slate-800 flex flex-col h-screen sticky top-0">
        <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 flex items-center justify-center text-indigo-400">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <div class="brand text-lg font-bold leading-tight">IA DIAL</div>
                <div class="text-slate-500 text-xs">Espace administrateur</div>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1">
            @php
                $links = [
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'fa-solid fa-chart-line'],
                    ['route' => 'admin.clients', 'label' => 'Clients', 'icon' => 'fa-solid fa-users'],
                    ['route' => 'admin.appointments', 'label' => 'Rendez-vous', 'icon' => 'fa-solid fa-calendar-check'],
                    ['route' => 'admin.tests', 'label' => 'Essais', 'icon' => 'fa-solid fa-flask'],
                ];
            @endphp
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs($link['route']) || request()->routeIs($link['route'].'.*') ? 'bg-indigo-600/15 text-indigo-300 font-medium' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <i class="{{ $link['icon'] }} w-4 text-center"></i>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="px-3 py-4 border-t border-slate-800">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:bg-slate-900 hover:text-white transition">
                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                    <span>Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>
    {{-- Main content --}}
    <div class="flex-1 min-w-0">
        <header class="border-b border-slate-800 px-8 py-5">
            <h1 class="text-xl font-bold">@yield('title', 'Dashboard')</h1>
        </header>
        <main class="p-8 space-y-8">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>