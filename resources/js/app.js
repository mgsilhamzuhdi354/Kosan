

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

const THEME_STORAGE_KEY = 'kos-theme-color';
const DEFAULT_THEME = 'ocean';

function applyTheme(theme) {
    const selectedTheme = theme || DEFAULT_THEME;

    document.documentElement.dataset.theme = selectedTheme;
    localStorage.setItem(THEME_STORAGE_KEY, selectedTheme);

    document.querySelectorAll('[data-theme-choice]').forEach((button) => {
        const isActive = button.dataset.themeChoice === selectedTheme;
        button.classList.toggle('theme-swatch-active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
}

applyTheme(localStorage.getItem(THEME_STORAGE_KEY) || DEFAULT_THEME);

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-theme-toggle]');
    const choice = event.target.closest('[data-theme-choice]');
    const switcher = event.target.closest('.theme-switcher');

    if (toggle) {
        toggle.closest('.theme-switcher')?.classList.toggle('theme-switcher-open');
        return;
    }

    if (choice) {
        applyTheme(choice.dataset.themeChoice);
        choice.closest('.theme-switcher')?.classList.remove('theme-switcher-open');
        return;
    }

    if (! switcher) {
        document.querySelectorAll('.theme-switcher-open').forEach((panel) => panel.classList.remove('theme-switcher-open'));
    }
});

Alpine.start();
