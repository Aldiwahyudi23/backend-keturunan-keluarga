<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }


    /*
    |--------------------------------------------------------------------------
    | Relations Book
    |--------------------------------------------------------------------------
    */
    public function createdBooks(): HasMany
    {
        return $this->hasMany(Book::class, 'created_by');
    }

    public function updatedBooks(): HasMany
    {
        return $this->hasMany(Book::class, 'updated_by');
    }

    public function createdBookSections(): HasMany
    {
        return $this->hasMany(BookSection::class, 'created_by');
    }

    public function updatedBookSections(): HasMany
    {
        return $this->hasMany(BookSection::class, 'updated_by');
    }
    
}
