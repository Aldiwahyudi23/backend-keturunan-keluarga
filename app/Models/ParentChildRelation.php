<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentChildRelation extends Model
{
    protected $fillable = [
        'parent_id',
        'child_id',
        'type',
    ];

    protected $appends = [
        'type_label',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function parent()
    {
        return $this->belongsTo(
            Person::class,
            'parent_id'
        );
    }

    public function child()
    {
        return $this->belongsTo(
            Person::class,
            'child_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {

            'biological' => 'Anak Kandung',

            'adopted' => 'Anak Angkat',

            'step' => 'Anak Tiri',

            default => '-',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public static function getTypeOptions(): array
    {
        return [
            'biological' => 'Anak Kandung',
            'adopted' => 'Anak Angkat',
            'step' => 'Anak Tiri',
        ];
    }
}