<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
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
                $person->person_code = 'PRS'.Str::random(6); // temporary
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

    /**
     * Relasi ke parent biologis perempuan (Ibu).
     */
    public function motherRelation(): HasOne
    {
        return $this->hasOne(
            ParentChildRelation::class,
            'child_id',
            'id'
        )
            ->where('type', 'biological')
            ->whereHas('parent', function ($query) {
                $query->where('gender', 'female');
            });
    }

    /**
     * Ambil data ibu biologis.
     */
    public function getMotherAttribute(): ?Person
    {
        return $this->motherRelation?->parent;
    }

    /**
     * Relasi ke parent biologis laki-laki (Ayah).
     */
    public function fatherRelation(): HasOne
    {
        return $this->hasOne(
            ParentChildRelation::class,
            'child_id',
            'id'
        )
            ->where('type', 'biological')
            ->whereHas('parent', function ($query) {
                $query->where('gender', 'male');
            });
    }

    /**
     * Ambil data ayah biologis.
     */
    public function getFatherAttribute(): ?Person
    {
        return $this->fatherRelation?->parent;
    }

    /**
     * Bin atau binti.
     */
    public function getNasabAttribute(): ?string
    {
        if (! $this->father) {
            return null;
        }

        return match ($this->gender) {
            'male' => 'bin',
            'female' => 'binti',
            default => null,
        };
    }

    /**
     * Nama lengkap beserta bin/binti.
     *
     * Contoh:
     * Ahmad bin Abdullah
     * Siti binti Abdullah
     */
    public function getFullNameWithNasabAttribute(): string
    {
        $father = $this->father;

        if (! $father) {
            return $this->full_name;
        }

        $connector = match ($this->gender) {
            'male' => 'bin',
            'female' => 'binti',
            default => '',
        };

        if ($connector === '') {
            return $this->full_name;
        }

        return "{$this->full_name} {$connector} {$father->full_name}";
    }

    public function histories()
    {
        return $this->hasMany(PersonHistory::class)
            ->orderBy('sort');
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

    /**
     * Relasi utama untuk RelationManager "marriages".
     * Mengambil SEMUA data pernikahan dimana person ini berperan
     * sebagai suami ATAU sebagai istri.
     *
     * PERBAIKAN: Menggunakan union atau two separate relations
     */
    public function marriages(): HasMany
    {
        // Gunakan pendekatan yang lebih baik dengan dua relasi terpisah
        if ($this->gender === 'male') {
            return $this->hasMany(Marriage::class, 'husband_id');
        } elseif ($this->gender === 'female') {
            return $this->hasMany(Marriage::class, 'wife_id');
        }

        // Fallback: jika gender tidak ditentukan
        return $this->hasMany(Marriage::class, 'husband_id')
            ->where(function ($query) {
                $query->where('husband_id', $this->id)
                    ->orWhere('wife_id', $this->id);
            });
    }

    /**
     * Relasi pernikahan dimana person ini adalah suami.
     */
    public function marriagesAsHusband(): HasMany
    {
        return $this->hasMany(Marriage::class, 'husband_id');
    }

    /**
     * Relasi pernikahan dimana person ini adalah istri.
     */
    public function marriagesAsWife(): HasMany
    {
        return $this->hasMany(Marriage::class, 'wife_id');
    }

    /**
     * Helper: ambil koleksi Person pasangan (suami/istri lawan) dari semua
     * pernikahan yang tercatat, otomatis menyesuaikan gender person ini.
     * Contoh: $person->spouses untuk laki-laki akan berisi semua istri,
     * untuk perempuan akan berisi semua suami.
     */
    public function getSpousesAttribute(): Collection
    {
        // Jika gender laki-laki, ambil semua istri dari marriagesAsHusband
        if ($this->gender === 'male') {
            return $this->marriagesAsHusband()
                ->with('wife')
                ->get()
                ->pluck('wife')
                ->filter();
        }

        // Jika gender perempuan, ambil semua suami dari marriagesAsWife
        if ($this->gender === 'female') {
            return $this->marriagesAsWife()
                ->with('husband')
                ->get()
                ->pluck('husband')
                ->filter();
        }

        // Fallback: jika gender tidak ditentukan
        return collect();
    }

    /**
     * Helper: ambil pasangan aktif (belum cerai)
     */
    public function getActiveSpouseAttribute(): ?Person
    {
        // Jika gender laki-laki, cari istri aktif
        if ($this->gender === 'male') {
            $marriage = $this->marriagesAsHusband()
                ->whereNull('divorce_date')
                ->with('wife')
                ->first();

            return $marriage ? $marriage->wife : null;
        }

        // Jika gender perempuan, cari suami aktif
        if ($this->gender === 'female') {
            $marriage = $this->marriagesAsWife()
                ->whereNull('divorce_date')
                ->with('husband')
                ->first();

            return $marriage ? $marriage->husband : null;
        }

        return null;
    }

    /**
     * Helper: cek apakah person ini sedang terikat
     * pernikahan aktif (belum cerai).
     */
    public function hasActiveMarriage(): bool
    {
        if ($this->gender === 'male') {
            return $this->marriagesAsHusband()->whereNull('divorce_date')->exists();
        }

        if ($this->gender === 'female') {
            return $this->marriagesAsWife()->whereNull('divorce_date')->exists();
        }

        return false;
    }

    /**
     * Helper: ambil semua pasangan (termasuk yang sudah cerai)
     */
    public function getAllSpousesAttribute(): Collection
    {
        if ($this->gender === 'male') {
            return $this->marriagesAsHusband()
                ->with('wife')
                ->get()
                ->pluck('wife')
                ->filter();
        }

        if ($this->gender === 'female') {
            return $this->marriagesAsWife()
                ->with('husband')
                ->get()
                ->pluck('husband')
                ->filter();
        }

        return collect();
    }

    /**
     * Helper: cek apakah seseorang memiliki pasangan (aktif atau tidak)
     */
    public function hasSpouse(): bool
    {
        if ($this->gender === 'male') {
            return $this->marriagesAsHusband()->exists();
        }

        if ($this->gender === 'female') {
            return $this->marriagesAsWife()->exists();
        }

        return false;
    }

    /**
     * Relasi dengan Book, untuk menandai bahwa person ini adalah root person dari buku tertentu.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'root_person_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(PersonActivity::class, 'person_id')
            ->orderBy('created_at', 'desc');
    }
}
