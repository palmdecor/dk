@props(['type' => 'info'])
<div {{ $attributes->merge(['class' => 'alert alert-'.$type.' rounded-3 shadow-sm']) }}>
    {{ $slot }}
</div>
