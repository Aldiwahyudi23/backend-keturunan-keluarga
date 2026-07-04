<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Informasi Buku
            |--------------------------------------------------------------------------
            */

            $table->string('title');
            $table->string('edition')->nullable();
            $table->string('version')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Root Person
            |--------------------------------------------------------------------------
            */

            $table->foreignId('root_person_id')
                ->constrained('people')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Template
            |--------------------------------------------------------------------------
            */

            $table->foreignId('template_id')
                ->nullable()
                ->constrained('book_templates')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Cover
            |--------------------------------------------------------------------------
            */

            $table->string('cover_logo')->nullable();

            $table->string('cover_background')->nullable();

            $table->string('cover_title')
                ->default('BUKU SILSILAH KETURUNAN');

            $table->string('cover_subtitle')
                ->nullable();

            $table->text('cover_quote')
                ->nullable();

            $table->text('cover_footer')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Konfigurasi
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('default_max_generation')
                ->default(0)
                ->comment('0 = semua generasi');

            $table->boolean('show_cover')
                ->default(true);

            $table->boolean('show_table_of_contents')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'published',
                'archived'
            ])->default('draft');

            $table->timestamp('published_at')->nullable();

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
        Schema::dropIfExists('books');
    }
};