@props(['label', 'icon', 'type' => 'button'])

<button {{ $attributes->merge(['type' => $type, 'class' => 'btn btn-primary btn-sm', 'aria-label' => $label, 'title' => $label]) }}>
    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
</button>
