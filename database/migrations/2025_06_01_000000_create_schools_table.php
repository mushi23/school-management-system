<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('registration_number')->nullable();
            $table->string('county')->nullable();
            $table->string('sub_county')->nullable();
            $table->string('location')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('type')->nullable();
            $table->string('region')->nullable();
            $table->string('website')->nullable();
            $table->json('streams')->nullable();
            $table->json('levels')->nullable();
            $table->json('services')->nullable();
            $table->json('custom')->nullable();
            $table->string('logo')->nullable();
            $table->string('background')->nullable();
            $table->string('brand_color')->nullable();
            $table->string('slogan')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('is_setup_complete')->default(false);
            $table->string('curriculum_key')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // adds deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
