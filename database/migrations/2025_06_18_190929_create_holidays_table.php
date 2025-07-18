<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date')->unique();
            $table->string('country_code')->default('KE');
            $table->string('type')->nullable(); // e.g., Public, Observance
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('holidays');
    }
};
