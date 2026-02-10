@props(['field'])

<div>
    @error($field)
        <p {{ $attributes->merge(['class' => 'text-danger', 'mt-2']) }}>{{ $message }}</p>
    @enderror
</div>