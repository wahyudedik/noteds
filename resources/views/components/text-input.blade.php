@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-blue-500 focus:ring focus:ring-blue-500/20 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed'
]) }}>
