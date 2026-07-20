@extends('layouts.app')

@section('title', "Demande de devis Business - IADial")

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-16 pb-24">
    <div class="reveal text-center mb-10">
        <h1 class="font-display text-2xl sm:text-3xl font-bold">Demande de devis — IADial Business</h1>
        <p class="mt-3 text-white/50 text-sm sm:text-base">
            Sélectionnez les fonctionnalités qui vous intéressent, notre équipe vous recontacte avec un devis personnalisé.
        </p>
    </div>

    @if ($errors->any())
        <div class="reveal mb-6 rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('devis.store') }}" method="POST" class="reveal space-y-5 rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
        @csrf
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-white/60 mb-1.5">Nom *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-1.5">Entreprise</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}"
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-white/60 mb-1.5">E-mail *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-1.5">Téléphone *</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">
            </div>
        </div>

        <div>
            <label class="block text-sm text-white/60 mb-3">Fonctionnalités souhaitées</label>
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach ($options as $key => $label)
                    <label class="flex items-center gap-2.5 rounded-lg border border-white/10 bg-black/20 px-4 py-3 text-sm text-white/70 hover:border-sky-400/30 cursor-pointer">
                        <input type="checkbox" name="options[]" value="{{ $key }}"
                               class="rounded border-white/20 bg-black/40 text-sky-400 focus:ring-sky-400">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-sm text-white/60 mb-1.5">Précisez votre besoin</label>
            <textarea name="message" rows="4"
                      class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">{{ old('message') }}</textarea>
        </div>

        <button type="submit"
                class="btn-shine w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                    px-6 py-3.5 text-sm font-semibold text-black">
            Envoyer ma demande
        </button>
    </form>
</section>
@endsection