<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Book extends Model
{
    use SoftDeletes;

    protected $table = 'books';

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Informasi Buku
        |--------------------------------------------------------------------------
        */

        'title',
        'edition',
        'version',

        /*
        |--------------------------------------------------------------------------
        | Root Person
        |--------------------------------------------------------------------------
        */

        'root_person_id',

        /*
        |--------------------------------------------------------------------------
        | Template
        |--------------------------------------------------------------------------
        */

        'template_id',

        /*
        |--------------------------------------------------------------------------
        | Cover
        |--------------------------------------------------------------------------
        */

        'cover_logo',
        'cover_background',
        'cover_title',
        'cover_subtitle',
        'cover_quote',
        'cover_footer',

        /*
        |--------------------------------------------------------------------------
        | Config
        |--------------------------------------------------------------------------
        */

        'default_max_generation',
        'show_cover',
        'show_table_of_contents',

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'status',
        'published_at',

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_max_generation' => 'integer',
        'show_cover' => 'boolean',
        'show_table_of_contents' => 'boolean',

        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (Auth::check()) {
                $book->created_by = Auth::id();
                $book->updated_by = Auth::id();
            }
        });

        static::updating(function (Book $book) {
            if (Auth::check()) {
                $book->updated_by = Auth::id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function rootPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'root_person_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BookTemplate::class, 'template_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(BookSection::class)
            ->orderBy('sort');
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}