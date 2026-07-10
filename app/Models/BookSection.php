<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookSection extends Model
{
    use SoftDeletes;

    protected $table = 'book_sections';

    protected $fillable = [

        'book_id',

        'type',
        'key',

        'title',
        'content',

        'image',

        'options',

        'sort',

        'is_active',

        'created_by',
        'updated_by',

    ];

    protected $casts = [

        'options' => 'array',

        'is_active' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Type Constants
    |--------------------------------------------------------------------------
    */
    public function isText(): bool
    {
        return $this->type === 'text';
    }

    public function isDynamic(): bool
    {
        return $this->type === 'dynamic';
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isPageBreak(): bool
    {
        return $this->type === 'page_break';
    }
}
