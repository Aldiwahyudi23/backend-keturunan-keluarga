<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Spatie\EloquentSortable\SortableTrait;

class ParentChildRelation extends Model
{

    // use SortableTrait;

    protected $fillable = [
        'parent_id',
        'child_id',
        'type',
        'sort'
    ];

    protected $appends = [
        'type_label',
    ];

        /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    // Konfigurasi sortable
    public $sortable = [
        'order_column_name' => 'sort', // nama kolom sorting
        'sort_when_creating' => true,  // otomatis diurutkan saat create
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