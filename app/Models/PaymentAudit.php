<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id', 'action', 'performed_by', 'remarks'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}

