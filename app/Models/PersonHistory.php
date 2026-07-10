<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonHistory extends Model
{
    protected $fillable = [
        'person_id',
        'event_date',
        'title',
        'description',
        'location',
        'sort',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
