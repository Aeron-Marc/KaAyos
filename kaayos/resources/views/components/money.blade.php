@props(['value' => null, 'decimals' => 2])

@if($value === null || $value === '')
    <span {{ $attributes }}>{{ __('admin.common.not_available') }}</span>
@else
    <span {{ $attributes }}>&#8369;{{ number_format((float) $value, (int) $decimals) }}</span>
@endif
