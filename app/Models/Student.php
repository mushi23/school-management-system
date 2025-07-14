<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'admission_number',
        'dob',
        'gender',
        'school_id',
        'structure_id',
        'stream',
        'profile_picture',
        'email',
        'subjects',
    ];

    protected $casts = [
        'subjects' => 'array',
    ];


    public function structure()
    {
        return $this->belongsTo(SchoolStructure::class, 'structure_id');
    }
    
    public function guardians()
    {
        return $this->hasMany(Guardian::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return Storage::url($this->profile_picture);
        }
        return null; // Return null instead of default image URL
    }
}

