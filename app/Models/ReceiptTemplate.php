<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReceiptTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'name', 'header_html', 'footer_html', 'logo_path', 'slogan',
        'primary_font', 'secondary_font', 'primary_color', 'secondary_color', 'accent_color',
        'logo_width', 'logo_height', 'show_school_logo', 'show_school_slogan',
        'show_school_address', 'show_school_phone', 'show_school_email',
        'show_payment_method', 'show_receipt_number', 'show_payment_date',
        'show_student_details', 'show_fee_breakdown', 'show_balance',
        'custom_css', 'layout_settings', 'is_active',
        // Security features
        'signature_path', 'signature_name', 'signature_title', 'show_signature', 'signature_position',
        'seal_path', 'show_seal', 'seal_position', 'seal_size', 'seal_style',
        'watermark_text', 'show_watermark', 'watermark_color', 'watermark_opacity', 'watermark_position', 'watermark_rotation',
        'show_qr_code', 'qr_code_position', 'qr_code_size', 'qr_code_data',
        'show_security_features', 'security_text', 'show_verification_url', 'verification_url', 'show_unique_id', 'unique_id_prefix',
        'receipt_number_format', 'receipt_number_start', 'receipt_number_prefix'
    ];

    protected $casts = [
        'logo_width' => 'decimal:2',
        'logo_height' => 'decimal:2',
        'show_school_logo' => 'boolean',
        'show_school_slogan' => 'boolean',
        'show_school_address' => 'boolean',
        'show_school_phone' => 'boolean',
        'show_school_email' => 'boolean',
        'show_payment_method' => 'boolean',
        'show_receipt_number' => 'boolean',
        'show_payment_date' => 'boolean',
        'show_student_details' => 'boolean',
        'show_fee_breakdown' => 'boolean',
        'show_balance' => 'boolean',
        'layout_settings' => 'array',
        'is_active' => 'boolean',
        // Security features casts
        'show_signature' => 'boolean',
        'show_seal' => 'boolean',
        'seal_size' => 'decimal:2',
        'show_watermark' => 'boolean',
        'watermark_opacity' => 'decimal:2',
        'watermark_rotation' => 'integer',
        'show_qr_code' => 'boolean',
        'qr_code_size' => 'decimal:2',
        'show_security_features' => 'boolean',
        'show_verification_url' => 'boolean',
        'show_unique_id' => 'boolean',
        'receipt_number_start' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * Get the logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return \Storage::url($this->logo_path);
        }
        return null;
    }

    /**
     * Get the signature URL
     */
    public function getSignatureUrlAttribute()
    {
        if ($this->signature_path) {
            return \Storage::url($this->signature_path);
        }
        return null;
    }

    /**
     * Get the seal URL
     */
    public function getSealUrlAttribute()
    {
        if ($this->seal_path) {
            return \Storage::url($this->seal_path);
        }
        return null;
    }

    /**
     * Get default template for a school
     */
    public static function getDefaultForSchool($schoolId)
    {
        return static::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create default template for a school
     */
    public static function createDefaultForSchool($schoolId)
    {
        $school = School::find($schoolId);
        
        return static::create([
            'school_id' => $schoolId,
            'name' => 'Default Receipt Template',
            'slogan' => $school->slogan ?? 'Excellence in Education',
            'primary_font' => 'Arial, sans-serif',
            'secondary_font' => 'Georgia, serif',
            'primary_color' => '#1f2937',
            'secondary_color' => '#3b82f6',
            'accent_color' => '#10b981',
            'logo_width' => 150.00,
            'logo_height' => 50.00,
            'show_school_logo' => true,
            'show_school_slogan' => true,
            'show_school_address' => true,
            'show_school_phone' => true,
            'show_school_email' => true,
            'show_payment_method' => true,
            'show_receipt_number' => true,
            'show_payment_date' => true,
            'show_student_details' => true,
            'show_fee_breakdown' => true,
            'show_balance' => true,
            'is_active' => true,
            // Default security settings
            'show_signature' => false,
            'signature_position' => 'bottom-right',
            'show_seal' => false,
            'seal_position' => 'bottom-left',
            'seal_size' => 40.00,
            'show_watermark' => false,
            'watermark_text' => 'ORIGINAL',
            'watermark_color' => '#e5e7eb',
            'watermark_opacity' => 0.15,
            'watermark_position' => 'center',
            'watermark_rotation' => -45,
            'show_qr_code' => false,
            'qr_code_position' => 'top-right',
            'qr_code_size' => 30.00,
            'show_security_features' => false,
            'security_text' => 'This receipt is computer generated and authentic',
            'show_verification_url' => false,
            'show_unique_id' => false,
            'receipt_number_format' => 'SCH{year}{sequence}',
            'receipt_number_start' => 1,
        ]);
    }

    /**
     * Get CSS styles for the template
     */
    public function getCssStyles()
    {
        $baseCss = "
            .receipt-container {
                font-family: {$this->primary_font};
                color: {$this->primary_color};
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                position: relative;
            }
            .receipt-header {
                text-align: center;
                border-bottom: 2px solid {$this->secondary_color};
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .school-logo {
                max-width: {$this->logo_width}mm;
                max-height: {$this->logo_height}mm;
                margin: 0 auto 10px;
            }
            .school-name {
                font-family: {$this->secondary_font};
                font-size: 24px;
                font-weight: bold;
                color: {$this->primary_color};
                margin-bottom: 5px;
            }
            .school-slogan {
                font-style: italic;
                color: {$this->secondary_color};
                margin-bottom: 10px;
            }
            .school-info {
                font-size: 12px;
                color: #666;
                line-height: 1.4;
            }
            .receipt-title {
                font-size: 20px;
                font-weight: bold;
                text-align: center;
                color: {$this->accent_color};
                margin: 20px 0;
            }
            .receipt-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 30px;
            }
            .detail-group {
                margin-bottom: 15px;
            }
            .detail-label {
                font-weight: bold;
                color: {$this->primary_color};
                margin-bottom: 5px;
            }
            .detail-value {
                color: #333;
            }
            .fee-breakdown {
                border: 1px solid #ddd;
                border-radius: 5px;
                overflow: hidden;
                margin-bottom: 20px;
            }
            .fee-header {
                background-color: {$this->secondary_color};
                color: white;
                padding: 10px 15px;
                font-weight: bold;
            }
            .fee-row {
                display: grid;
                grid-template-columns: 2fr 1fr;
                padding: 10px 15px;
                border-bottom: 1px solid #eee;
            }
            .fee-row:last-child {
                border-bottom: none;
            }
            .fee-row:nth-child(even) {
                background-color: #f9f9f9;
            }
            .amount-section {
                text-align: right;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 2px solid {$this->accent_color};
            }
            .total-amount {
                font-size: 18px;
                font-weight: bold;
                color: {$this->accent_color};
            }
            .receipt-footer {
                margin-top: 40px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            /* Security Features */
            .watermark {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate({$this->watermark_rotation}deg);
                font-size: 48px;
                font-weight: bold;
                color: {$this->watermark_color};
                opacity: {$this->watermark_opacity};
                pointer-events: none;
                z-index: 1;
            }
            .qr-code {
                position: absolute;
                top: 20px;
                right: 20px;
                width: {$this->qr_code_size}mm;
                height: {$this->qr_code_size}mm;
            }
            .signature-section {
                margin-top: 40px;
                text-align: right;
            }
            .signature-image {
                max-width: 120px;
                max-height: 60px;
                margin-bottom: 10px;
            }
            .signature-name {
                font-weight: bold;
                color: {$this->primary_color};
            }
            .signature-title {
                font-size: 12px;
                color: #666;
            }
            .seal-image {
                position: absolute;
                bottom: 20px;
                left: 20px;
                width: {$this->seal_size}mm;
                height: {$this->seal_size}mm;
            }
            .security-text {
                font-size: 10px;
                color: #999;
                text-align: center;
                margin-top: 20px;
            }
        ";

        return $baseCss . ($this->custom_css ?? '');
    }
}
