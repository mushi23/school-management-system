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
        Schema::create('school_mpesa_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('shortcode');
            $table->string('passkey');
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->string('callback_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Ensure one active setting per school
            $table->unique(['school_id', 'is_active'], 'unique_active_school_mpesa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_mpesa_settings');
    }
};
