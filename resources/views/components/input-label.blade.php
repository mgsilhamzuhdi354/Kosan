@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-extrabold text-slate-700']) }}>
    {{ $value ?? $slot }}
</label>
