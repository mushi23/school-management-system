<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->after('profile_picture');
            $table->string('teacher_number')->nullable()->after('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });

        // Backfill teacher_number for existing teachers
        $teachers = DB::table('teachers')->get();
        foreach ($teachers as $teacher) {
            $number = 'TCH-' . now()->year . '-' . strtoupper(Str::random(6));
            DB::table('teachers')->where('id', $teacher->id)->update(['teacher_number' => $number]);
        }

        // Now set teacher_number as not nullable and unique
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('teacher_number')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
            $table->dropColumn('teacher_number');
        });
    }
};
