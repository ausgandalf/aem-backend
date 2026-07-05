<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'name',
    'country',
    'type',
    'note',
    'legal_status',
    'register_no',
    'metadata',
])]
class Organization extends Model
{
    //
}
