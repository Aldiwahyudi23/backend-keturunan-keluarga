<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'book_templates';

    protected $fillable = [

        'name',
        'blade_view',
        'description',
        'is_active',

    ];

    protected $casts = [

        'is_active' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'template_id');
    }

    public function getBladeViewAttribute(): string
    {
        return match ($this->key) {
            'classic' => 'pdf.book.classic',
            'modern' => 'pdf.book.modern',
            'minimal' => 'pdf.book.minimal',
            'premium' => 'pdf.book.premium',
            default => 'pdf.book.classic',
        };
    }
}