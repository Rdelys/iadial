<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion admin — IA DIAL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .brand { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="brand text-2xl font-bold text-white">IA DIAL</div>
            <p class="text-slate-500 text-sm mt-1">Espace administrateur</p>
        </div>

        <form method="POST" action="{{ route('admin.login.submit') }}"
              class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">
            @csrf

            <label class="block text-sm text-slate-400 mb-2">Code de vérification</label>
            <input type="password" name="code" inputmode="numeric" autofocus maxlength="20"
                   class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white text-center text-2xl tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="••••">

            @error('code')
                <p class="text-red-400 text-sm mt-3">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="w-full mt-6 bg-indigo-600 hover:bg-indigo-500 transition text-white font-medium py-3 rounded-lg">
                Se connecter
            </button>
        </form>
    </div>
</body>
</html>