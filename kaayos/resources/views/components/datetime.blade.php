@props(['value' => null, 'format' => 'M j, Y', 'fallback' => null])

@php
    $date = $value instanceof \DateTimeInterface
        ? \Illuminate\Support\Carbon::instance($value)
        : ($value ? \Illuminate\Support\Carbon::parse($value) : null);
@endphp

@if($date)
    <time {{ $attributes }} datetime="{{ $date->toIso8601String() }}">{{ $date->translatedFormat($format) }}</time>
@else
    <span {{ $attributes }}>{{ $fallback ?? __('admin.common.not_available') }}</span>
@endif
