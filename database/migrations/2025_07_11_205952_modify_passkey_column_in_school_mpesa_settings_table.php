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
        Schema::table('school_mpesa_settings', function (Blueprint $table) {
            $table->text('passkey')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_mpesa_settings', function (Blueprint $table) {
            $table->string('passkey')->change();
        });
    }
};
