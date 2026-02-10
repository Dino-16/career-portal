@php
    $setting = \App\Models\Admin\HoneypotSetting::first();
    $isEnabled = $setting ? $setting->is_enabled : false;
    $fieldName = $setting ? $setting->field_name : 'secondary_email';
@endphp

@if($isEnabled)
    <div style="display: none; visibility: hidden; opacity: 0; position: absolute; left: -9999px;">
        <label for="{{ $fieldName }}">Please leave this field blank</label>
        <input type="text" 
               id="{{ $fieldName }}" 
               name="{{ $fieldName }}" 
               wire:model="honeypot_field" 
               tabindex="-1" 
               autocomplete="off">
    </div>
@endif
