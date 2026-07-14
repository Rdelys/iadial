{{-- resources/views/components/tarif-check.blade.php --}}
@props(['inline' => false])

<svg class="{{ $inline ? 'inline-block' : 'mt-0.5 shrink-0' }} w-4 h-4 text-sky-400"
     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
</svg>