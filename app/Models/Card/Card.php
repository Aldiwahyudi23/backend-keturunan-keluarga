<?php

namespace App\Models\Card;

use App\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Card extends Model
{
    use SoftDeletes;

    protected $table = 'cards';

    protected $fillable = [
        'uuid',
        'card_template_id',
        'name',
        'title',
        'subtitle',
        'logo_path',
        'background_path',
        'note',
        'root_person_id',
        'status',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(function (Card $card) {
            if (empty($card->uuid)) {
                $card->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CardTemplate::class, 'card_template_id');
    }

    public function rootPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'root_person_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CardContact::class, 'card_id');
    }

    public function cardPeople(): HasMany
    {
        return $this->hasMany(CardPeople::class, 'card_id');
    }
}
