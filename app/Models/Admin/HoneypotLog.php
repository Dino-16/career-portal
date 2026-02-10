<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoneypotLog extends Model
{
    use HasFactory;

    protected $fillable = ['ip_address', 'user_agent', 'form_name', 'payload'];
}
