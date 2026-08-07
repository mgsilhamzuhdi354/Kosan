<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-2xl border border-transparent bg-sky-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-sky-200 focus:outline-none focus:ring-4 focus:ring-sky-100 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
