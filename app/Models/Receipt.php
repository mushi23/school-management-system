<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'student_id', 'payment_id', 'receipt_template_id',
        'receipt_number', 'amount_paid', 'payment_method', 'payment_reference',
        'payment_date', 'term', 'academic_year', 'balance_before', 'balance_after',
        'fee_breakdown', 'notes', 'generated_by', 'pdf_path', 'receipt_data'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'payment_date' => 'date',
        'fee_breakdown' => 'array',
        'receipt_data' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function template()
    {
        return $this->belongsTo(ReceiptTemplate::class, 'receipt_template_id');
    }

    /**
     * Generate a unique receipt number
     */
    public static function generateReceiptNumber($schoolId)
    {
        $year = date('Y');
        $month = date('m');
        $school = School::find($schoolId);
        $schoolCode = Str::upper(Str::substr($school->name ?? 'SCH', 0, 3));
        
        // Get the last receipt number for this school and month
        $lastReceipt = static::where('school_id', $schoolId)
            ->where('receipt_number', 'like', "{$schoolCode}{$year}{$month}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = (int) Str::substr($lastReceipt->receipt_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $schoolCode . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create receipt from payment
     */
    public static function createFromPayment(Payment $payment, $term = null, $academicYear = null)
    {
        $student = $payment->student;
        $school = $student->school;
        
        // Get or create default template
        $template = ReceiptTemplate::getDefaultForSchool($school->id);
        if (!$template) {
            $template = ReceiptTemplate::createDefaultForSchool($school->id);
        }

        // Calculate balances
        $balanceBefore = $payment->invoice ? $payment->invoice->amount_due - $payment->invoice->amount_paid + $payment->amount : 0;
        $balanceAfter = $balanceBefore - $payment->amount;

        // Get fee breakdown from invoice if available
        $feeBreakdown = [];
        if ($payment->invoice) {
            $feeBreakdown = [
                [
                    'item' => 'School Fees - ' . ($term ?? 'Current Term'),
                    'amount' => $payment->amount
                ]
            ];
        }

        $receipt = static::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'payment_id' => $payment->id,
            'receipt_template_id' => $template->id,
            'receipt_number' => static::generateReceiptNumber($school->id),
            'amount_paid' => $payment->amount,
            'payment_method' => $payment->method,
            'payment_reference' => $payment->reference,
            'payment_date' => $payment->payment_date ?? now(),
            'term' => $term,
            'academic_year' => $academicYear,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'fee_breakdown' => $feeBreakdown,
            'notes' => $payment->notes,
            'generated_by' => 'System',
            'receipt_data' => [
                'student_name' => $student->name,
                'student_number' => $student->student_number ?? 'N/A',
                'school_name' => $school->name,
                'school_address' => $school->location ?? 'N/A',
                'school_phone' => $school->contact_phone ?? 'N/A',
                'school_email' => $school->contact_email ?? 'N/A',
            ]
        ]);

        return $receipt;
    }

    /**
     * Get PDF URL
     */
    public function getPdfUrlAttribute()
    {
        if ($this->pdf_path) {
            return \Storage::url($this->pdf_path);
        }
        return null;
    }

    /**
     * Scope to filter by term and academic year
     */
    public function scopeByTermAndYear($query, $term, $academicYear)
    {
        return $query->where('term', $term)
                    ->where('academic_year', $academicYear);
    }

    /**
     * Scope to filter by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by school
     */
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
