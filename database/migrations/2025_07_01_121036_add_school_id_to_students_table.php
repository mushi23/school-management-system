<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('students', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->foreignId('school_id')->after('admission_number')->constrained()->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('students', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
