<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolMpesaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'consumer_key',
        'consumer_secret',
        'shortcode',
        'passkey',
        'environment',
        'callback_url',
        'is_active',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'consumer_secret',
        'passkey',
    ];

    /**
     * Get the school that owns the M-PESA setting.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the active M-PESA setting for a school.
     */
    public static function getActiveForSchool($schoolId)
    {
        return static::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Deactivate all other settings for this school when activating a new one.
     */
    public function activate()
    {
        // Deactivate all other settings for this school
        static::where('school_id', $this->school_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        // Activate this setting
        $this->update(['is_active' => true]);
    }
}
