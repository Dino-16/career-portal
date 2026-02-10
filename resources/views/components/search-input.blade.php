@props(['disabled' => false, 'placeholder' => 'Search...'])

<div class="input-group" style="width: 350px;">
    <span class="input-group-text bg-white border-end-0">
        <i class="bi bi-search text-muted"></i>
    </span>
    <input 
        type="search" 
        @disabled($disabled) 
        {{ $attributes->merge(['class' => 'form-control border-start-0 ps-0']) }} 
        placeholder="{{ $placeholder }}"
    />
</div>
