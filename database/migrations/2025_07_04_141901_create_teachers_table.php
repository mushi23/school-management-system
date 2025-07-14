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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('profile_picture')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('teacher_number')->unique();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
