@php
    $currentLocale = app()->getLocale() === 'fil' ? 'fil' : 'en';
@endphp

<div class="relative inline-flex items-center text-xs font-semibold" style="display:inline-flex; align-items:center; font-size: 0.8125rem;">
    <div style="display:inline-flex; background: rgba(0,0,0,0.05); padding: 3px; border-radius: 9999px; border: 1px solid rgba(0,0,0,0.08);">
        <a href="{{ route('locale.switch', 'en') }}"
           style="padding: 4px 10px; border-radius: 9999px; text-decoration: none; transition: all 0.2s; {{ $currentLocale === 'en' ? 'background: #2563eb; color: #fff; font-weight: 700; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}"
           title="Switch to English">
            EN
        </a>
        <a href="{{ route('locale.switch', 'fil') }}"
           style="padding: 4px 10px; border-radius: 9999px; text-decoration: none; transition: all 0.2s; {{ $currentLocale === 'fil' ? 'background: #2563eb; color: #fff; font-weight: 700; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' }}"
           title="Lumipat sa Filipino">
            FIL
        </a>
    </div>
</div>

