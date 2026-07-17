@extends('layouts.app')

@section('title', "Connexion - IADial")

@section('content')
<section class="max-w-md mx-auto px-4 sm:px-6 pt-24 pb-24">
    <div class="reveal text-center mb-10">
        <h1 class="font-display text-2xl sm:text-3xl font-bold">Connexion à votre espace</h1>
        <p class="mt-3 text-white/50 text-sm">Accédez à votre compte IADial.</p>
    </div>

    @if ($errors->any())
        <div class="reveal mb-6 rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('connexion.submit') }}" method="POST" class="reveal space-y-5 rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
        @csrf
        <div>
            <label class="block text-sm text-white/60 mb-1.5">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">
        </div>
        <div>
            <label class="block text-sm text-white/60 mb-1.5">Mot de passe</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">
        </div>
        <label class="flex items-center gap-2 text-sm text-white/50">
            <input type="checkbox" name="remember" class="rounded border-white/20 bg-black/40 text-sky-400 focus:ring-sky-400">
            Se souvenir de moi
        </label>
        <button type="submit"
                class="btn-shine w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                    px-6 py-3 text-sm font-semibold text-black">
            Se connecter
        </button>
        <p class="text-center text-xs text-white/40">
            Pas encore de compte ?
            <a href="{{ route('tarifs') }}" class="text-sky-300 hover:underline">Choisir une offre</a>
        </p>
    </form>
</section>
@endsection