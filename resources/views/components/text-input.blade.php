@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-2xl border-slate-200 bg-white/95 shadow-sm focus:border-sky-400 focus:ring-sky-100']) }}>
