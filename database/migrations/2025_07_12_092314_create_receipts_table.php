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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('receipt_template_id')->nullable();
            $table->string('receipt_number')->unique(); // Auto-generated receipt number
            $table->decimal('amount_paid', 12, 2);
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->date('payment_date');
            $table->string('term')->nullable();
            $table->string('academic_year')->nullable();
            $table->decimal('balance_before', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->json('fee_breakdown')->nullable(); // JSON array of fee items
            $table->text('notes')->nullable();
            $table->string('generated_by')->nullable(); // User who generated the receipt
            $table->string('pdf_path')->nullable(); // Path to generated PDF
            $table->json('receipt_data')->nullable(); // JSON data used to generate receipt
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('receipt_template_id')->references('id')->on('receipt_templates')->onDelete('set null');
            
            // Indexes for better performance
            $table->index(['school_id', 'student_id']);
            $table->index(['payment_date', 'term', 'academic_year']);
            $table->index('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
