@props(['icon' => 'fa-inbox', 'title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon" aria-hidden="true"><i class="fa-solid {{ $icon }}"></i></div>
    <div class="empty-state-title">{{ $title }}</div>
    @if($subtitle)
        <div class="empty-state-subtitle">{{ $subtitle }}</div>
    @endif
    @if(! $slot->isEmpty())
        <div>{{ $slot }}</div>
    @endif
</div>
