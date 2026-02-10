<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoneypotSetting extends Model
{
    use HasFactory;

    protected $fillable = ['is_enabled', 'field_name'];
}
