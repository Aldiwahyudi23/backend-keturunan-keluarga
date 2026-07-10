<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonActivity extends Model
{
    protected $table = 'person_activities';

    protected $fillable = [
        'person_id',
        'description',
        'can_parent_view',
        'created_by',
    ];

    protected $casts = [
        'can_parent_view' => 'boolean',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
