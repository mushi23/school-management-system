<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['name', 'date', 'country_code', 'type'];
    protected $casts = [
        'date' => 'date',
    ];
}
