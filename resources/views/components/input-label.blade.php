@props(['value'])

<div>
    <label {{ $attributes->merge(['class' => 'form-label']) }}>{{ $value }}</label>
</div>