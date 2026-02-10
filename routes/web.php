<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\Website\Careers::class)->name('careers');
Route::get('/apply-now/{id}', App\Livewire\Website\ApplyNow::class)->name('apply-now');

