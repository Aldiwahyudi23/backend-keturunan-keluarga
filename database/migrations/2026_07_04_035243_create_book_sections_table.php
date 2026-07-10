<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_sections', function (Blueprint $table) {

            $table->id();

            $table->foreignId('book_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Jenis Section
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [
                'text',
                'dynamic',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Key Dynamic
            |--------------------------------------------------------------------------
            */

            $table->string('key')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Isi
            |--------------------------------------------------------------------------
            */

            $table->string('title')->nullable();

            $table->longText('content')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            $table->string('image')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Dynamic Options
            |--------------------------------------------------------------------------
            */

            $table->json('options')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sort')
                ->default(1);

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_sections');
    }
};
