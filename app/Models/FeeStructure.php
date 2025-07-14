<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'academic_year', 'term', 'amount', 'description'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function scholarships()
    {
        return $this->hasMany(Scholarship::class);
    }
}

