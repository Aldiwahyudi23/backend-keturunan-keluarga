<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_people', function (Blueprint $table) {
            $table->id();

            $table->foreignId('card_id')
                ->constrained('cards')
                ->cascadeOnDelete();

            $table->foreignId('person_id')
                ->constrained('people')
                ->cascadeOnDelete();

            $table->string('photo_path')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();

            $table->unique(['card_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_people');
    }
};
