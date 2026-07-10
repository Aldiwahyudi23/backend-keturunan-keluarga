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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('person_code')->unique();

            $table->string('full_name');
            $table->string('nickname')->nullable();

            $table->enum('gender', [
                'male',
                'female',
            ]);

            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();

            $table->string('birth_place')->nullable();
            $table->string('photo_path', 2048)->nullable();

            $table->text('bio')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
