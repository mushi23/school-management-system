<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'invoice_id', 'amount',
        'method', 'reference', 'payment_date', 'notes'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function audits()
    {
        return $this->hasMany(PaymentAudit::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }
}

