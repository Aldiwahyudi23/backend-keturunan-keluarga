<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Person extends Model
{
    use SoftDeletes;

    protected $table = 'people';

    protected $fillable = [
        'uuid', 
        'person_code',
        'full_name',
        'nickname',
        'gender',
        'birth_date',
        'death_date',
        'birth_place',
        'photo_path',
        'bio',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Person $person) {
            // Set UUID
            if (empty($person->uuid)) {
                $person->uuid = (string) Str::uuid();
            }
            
            // Set person_code sementara (akan di-update setelah created)
            if (empty($person->person_code)) {
                $person->person_code = 'PRS' . Str::random(6); // temporary
            }
        });

        static::created(function (Person $person) {
            // Update person_code dengan ID yang sudah ada
            $person->updateQuietly([
                'person_code' => sprintf('PRS%06d', $person->id),
            ]);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function histories()
    {
        return $this->hasMany(PersonHistory::class);
    }

    public function parentRelations()
    {
        return $this->hasMany(
            ParentChildRelation::class,
            'child_id'
        );
    }

    public function childRelations()
    {
        return $this->hasMany(
            ParentChildRelation::class,
            'parent_id'
        );
    }

    public function parents()
    {
        return $this->belongsToMany(
            Person::class,
            'parent_child_relations',
            'child_id',
            'parent_id'
        );
    }

    public function children()
    {
        return $this->belongsToMany(
            Person::class,
            'parent_child_relations',
            'parent_id',
            'child_id'
        );
    }

    public function husbandMarriages()
    {
        return $this->hasMany(
            Marriage::class,
            'husband_id'
        );
    }

    public function wifeMarriages()
    {
        return $this->hasMany(
            Marriage::class,
            'wife_id'
        );
    }
}