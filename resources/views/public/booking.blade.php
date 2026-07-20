<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver avec {{ $user->company_name ?? $user->name }} — IADial</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #05070A; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
        @keyframes pulseDot { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }
        .online-dot { animation: pulseDot 1.8s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen text-white flex flex-col items-center justify-center px-4 text-center">
    <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-xs text-sky-300 mb-4">
        <span class="relative flex h-2 w-2">
            <span class="online-dot absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
        </span>
        Réceptionniste IA &middot; en ligne
    </span>
    <h1 class="font-display text-2xl sm:text-3xl font-bold mb-3">{{ $user->company_name ?? $user->name }}</h1>
    <p class="text-white/50 text-sm max-w-sm mb-10">Discutez avec notre assistant pour prendre rendez-vous, en un instant.</p>

    <script src="https://unpkg.com/@vapi-ai/client-sdk-react/dist/embed/widget.umd.js" async type="text/javascript"></script>
    <vapi-widget
        public-key="{{ $user->vapi_public_key }}"
        assistant-id="{{ $user->vapi_assistant_id }}"
        mode="chat"
        theme="dark"
        base-bg-color="#0f172a"
        accent-color="#34E2C0"
        cta-button-color="#34E2C0"
        cta-button-text-color="#020617"
        border-radius="large"
        size="full"
        chat-first-message="Bonjour, comment puis-je vous aider à prendre rendez-vous ?"
        chat-placeholder="Écrivez votre message…"
    ></vapi-widget>

    <p class="text-white/20 text-xs mt-10">Propulsé par IADial</p>
</body>
</html>