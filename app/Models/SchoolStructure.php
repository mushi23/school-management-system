<?php

// app/Models/SchoolStructure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolStructure extends Model
{
    protected $fillable = [
        'school_id',
        'curriculum_level_id',
        'title',
        'abbreviation',
        'description',
        'order',
        'structure',
    ];

    protected $casts = [
        'structure' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function getCurrentTerm(): ?array
    {
        $terms = $this->structure['terms'] ?? [];
        $today = date('Y-m-d');
        foreach ($terms as $term) {
            if (!empty($term['start_date']) && !empty($term['end_date'])) {
                if ($today >= $term['start_date'] && $today <= $term['end_date']) {
                    return $term;
                }
            }
        }
        return null; // On holiday
    }
}
