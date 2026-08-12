@php
    $themes = [
        ['key' => 'ocean', 'label' => 'Biru', 'colors' => ['#075cd8', '#02b8a6']],
        ['key' => 'forest', 'label' => 'Hijau', 'colors' => ['#047857', '#84cc16']],
        ['key' => 'rose', 'label' => 'Rose', 'colors' => ['#be123c', '#f97316']],
        ['key' => 'violet', 'label' => 'Ungu', 'colors' => ['#6d28d9', '#ec4899']],
        ['key' => 'mono', 'label' => 'Elegan', 'colors' => ['#111827', '#64748b']],
    ];
@endphp

<div class="theme-switcher" aria-label="Pilih warna tema">
    <button type="button" class="theme-switcher-toggle" data-theme-toggle aria-label="Tampilkan pilihan warna">
        <span class="theme-switcher-current" aria-hidden="true"></span>
    </button>
    <div class="theme-switcher-panel" data-theme-panel>
        <p class="theme-switcher-title">Tema</p>
        <div class="theme-switcher-options">
            @foreach ($themes as $theme)
                <button
                    type="button"
                    class="theme-swatch"
                    data-theme-choice="{{ $theme['key'] }}"
                    aria-label="Pilih tema {{ $theme['label'] }}"
                    title="{{ $theme['label'] }}"
                    style="--swatch-from: {{ $theme['colors'][0] }}; --swatch-to: {{ $theme['colors'][1] }};"
                ></button>
            @endforeach
        </div>
    </div>
</div>
