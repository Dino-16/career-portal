<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';

    protected $fillable = [
        'requested_by',
        'department',
        'position',
        'opening',
    ];
}
