@props(['status', 'label' => null])

@php
    $status = (string) $status;
    $key = 'status.'.$status;
    $text = $label ?? (\Illuminate\Support\Facades\Lang::has($key) ? __($key) : str_replace('_', ' ', ucfirst($status)));
@endphp

<span {{ $attributes->merge(['class' => 'status-badge status-'.$status]) }}>{{ $text }}</span>
