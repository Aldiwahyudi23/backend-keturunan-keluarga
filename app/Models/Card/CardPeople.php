<?php

namespace App\Models\Card;

use App\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardPeople extends Model
{
    protected $table = 'card_people';

    protected $fillable = [
        'card_id',
        'person_id',
        'photo_path',
        'address',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
