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
        Schema::create('receipt_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name')->default('Default Receipt Template');
            $table->text('header_html')->nullable(); // Custom header HTML
            $table->text('footer_html')->nullable(); // Custom footer HTML
            $table->string('logo_path')->nullable(); // Path to school logo
            $table->string('slogan')->nullable(); // School slogan
            $table->string('primary_font')->default('Arial, sans-serif');
            $table->string('secondary_font')->default('Georgia, serif');
            $table->string('primary_color')->default('#1f2937'); // Dark gray
            $table->string('secondary_color')->default('#3b82f6'); // Blue
            $table->string('accent_color')->default('#10b981'); // Green
            $table->decimal('logo_width', 5, 2)->default(150.00); // Logo width in mm
            $table->decimal('logo_height', 5, 2)->default(50.00); // Logo height in mm
            $table->boolean('show_school_logo')->default(true);
            $table->boolean('show_school_slogan')->default(true);
            $table->boolean('show_school_address')->default(true);
            $table->boolean('show_school_phone')->default(true);
            $table->boolean('show_school_email')->default(true);
            $table->boolean('show_payment_method')->default(true);
            $table->boolean('show_receipt_number')->default(true);
            $table->boolean('show_payment_date')->default(true);
            $table->boolean('show_student_details')->default(true);
            $table->boolean('show_fee_breakdown')->default(true);
            $table->boolean('show_balance')->default(true);
            $table->text('custom_css')->nullable(); // Custom CSS styles
            $table->json('layout_settings')->nullable(); // JSON for layout preferences
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_templates');
    }
};
