<?php

namespace App\Models\Card;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'card_templates';

    protected $fillable = [
        'name',
        'view_path',
        'description',
        'preview',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'card_template_id');
    }
}
