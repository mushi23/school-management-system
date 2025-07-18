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
            $table->string('seal_style')->nullable()->after('seal_size'); // Track the style used for the seal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipt_templates', function (Blueprint $table) {
            $table->dropColumn('seal_style');
        });
    }
};
