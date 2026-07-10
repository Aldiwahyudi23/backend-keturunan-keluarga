<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marriage extends Model
{
    protected $fillable = [
        'husband_id',
        'wife_id',
        'marriage_date',
        'divorce_date',
        'notes',
    ];

    protected $casts = [
        'marriage_date' => 'date',
        'divorce_date' => 'date',
    ];

    public function husband()
    {
        return $this->belongsTo(
            Person::class,
            'husband_id'
        );
    }

    public function wife()
    {
        return $this->belongsTo(
            Person::class,
            'wife_id'
        );
    }
}
