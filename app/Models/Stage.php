<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = [
        'key',
        'label',
        'role',
        'order',
        'status',
    ];
}
