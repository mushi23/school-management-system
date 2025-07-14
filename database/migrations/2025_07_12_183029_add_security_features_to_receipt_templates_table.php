<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('receipt_templates', function (Blueprint $table) {
            // Digital Signature
            $table->string('signature_path')->nullable(); // Path to digital signature image
            $table->string('signature_name')->nullable(); // Name of the signatory
            $table->string('signature_title')->nullable(); // Title of the signatory (e.g., "Principal", "Bursar")
            $table->boolean('show_signature')->default(false);
            $table->string('signature_position')->default('bottom-right'); // Position of signature

            // School Seal/Stamp
            $table->string('seal_path')->nullable(); // Path to school seal/stamp image
            $table->boolean('show_seal')->default(false);
            $table->string('seal_position')->default('bottom-left'); // Position of seal
            $table->decimal('seal_size', 5, 2)->default(40.00); // Seal size in mm

            // Watermark
            $table->string('watermark_text')->nullable(); // Watermark text (e.g., "ORIGINAL", "PAID")
            $table->boolean('show_watermark')->default(false);
            $table->string('watermark_color')->default('#e5e7eb'); // Light gray
            $table->decimal('watermark_opacity', 3, 2)->default(0.15); // Opacity (0.00 to 1.00)
            $table->string('watermark_position')->default('center'); // Position of watermark
            $table->integer('watermark_rotation')->default(-45); // Rotation in degrees

            // QR Code
            $table->boolean('show_qr_code')->default(false);
            $table->string('qr_code_position')->default('top-right'); // Position of QR code
            $table->decimal('qr_code_size', 5, 2)->default(30.00); // QR code size in mm
            $table->text('qr_code_data')->nullable(); // Custom data for QR code

            // Security Features
            $table->boolean('show_security_features')->default(false);
            $table->string('security_text')->nullable(); // Security text (e.g., "This receipt is computer generated")
            $table->boolean('show_verification_url')->default(false);
            $table->string('verification_url')->nullable(); // URL for receipt verification
            $table->boolean('show_unique_id')->default(false);
            $table->string('unique_id_prefix')->nullable(); // Prefix for unique receipt IDs

            // Receipt Numbering
            $table->string('receipt_number_format')->default('SCH{year}{sequence}'); // Format for receipt numbers
            $table->integer('receipt_number_start')->default(1); // Starting number for sequence
            $table->string('receipt_number_prefix')->nullable(); // Prefix for receipt numbers
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipt_templates', function (Blueprint $table) {
            $table->dropColumn([
                'signature_path',
                'signature_name',
                'signature_title',
                'show_signature',
                'signature_position',
                'seal_path',
                'show_seal',
                'seal_position',
                'seal_size',
                'watermark_text',
                'show_watermark',
                'watermark_color',
                'watermark_opacity',
                'watermark_position',
                'watermark_rotation',
                'show_qr_code',
                'qr_code_position',
                'qr_code_size',
                'qr_code_data',
                'show_security_features',
                'security_text',
                'show_verification_url',
                'verification_url',
                'show_unique_id',
                'unique_id_prefix',
                'receipt_number_format',
                'receipt_number_start',
                'receipt_number_prefix'
            ]);
        });
    }
};
