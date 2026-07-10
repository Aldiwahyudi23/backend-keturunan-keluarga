<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('card_template_id')
                ->nullable()
                ->constrained('card_templates')
                ->nullOnDelete();

            $table->string('name');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('background_path')->nullable();

            $table->text('note')->nullable();

            $table->foreignId('root_person_id')
                ->constrained('people')
                ->cascadeOnDelete();

            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
