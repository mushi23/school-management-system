<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'registration_number',
        'county',
        'sub_county',
        'location',
        'contact_email',
        'contact_phone',
        'verified',

        // 🆕 Onboarding fields
       
        'curriculum_key',
        'curriculum_names',
        'facilities',
        'streams',
        'levels',
        'services',
        'logo',
        'background',
        'brand_color',
        'slogan',
        'is_setup_complete',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'streams' => 'array',
        'levels' => 'array',
        'services' => 'array',
        'custom' => 'array',
        'curriculum_names' => 'array',
        'facilities' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function getIsFullySetupAttribute(): bool
    {
        return $this->type &&
               $this->region &&
               $this->streams &&
               $this->levels &&
               $this->services &&
               $this->logo &&
               $this->brand_color;
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return \Storage::url($this->logo);
        }
        return null;
    }

    public function getBackgroundUrlAttribute()
    {
        if ($this->background) {
            return \Storage::url($this->background);
        }
        return null;
    }


    
    // app/Models/School.php
    public function adminUser()
    {
        return $this->hasOne(User::class)->whereHas('roles', function ($q) {
            $q->where('name', 'school_admin');
        });
    }
    
    public function structures()
    {
        return $this->hasMany(SchoolStructure::class);
    }

    public function mpesaSettings()
    {
        return $this->hasMany(SchoolMpesaSetting::class);
    }

    public function activeMpesaSetting()
    {
        return $this->hasOne(SchoolMpesaSetting::class)->where('is_active', true);
    }

    public function receiptTemplates()
    {
        return $this->hasMany(ReceiptTemplate::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

}
