{{-- resources/views/components/tarif-cross.blade.php --}}
@props(['inline' => false])

<svg class="{{ $inline ? 'inline-block' : 'mt-0.5 shrink-0' }} w-4 h-4 text-white/25"
     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
</svg>