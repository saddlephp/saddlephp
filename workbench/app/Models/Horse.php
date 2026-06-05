<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workbench\Database\Factories\HorseFactory;

class Horse extends Model
{
    /** @use HasFactory<HorseFactory> */
    use HasFactory;

    protected $fillable = ['name', 'breed', 'notes', 'is_saddled', 'rider_id', 'ranch_id', 'age', 'foaled_on', 'photo', 'last_vet_visit'];

    protected function casts(): array
    {
        return [
            'is_saddled' => 'boolean',
            'foaled_on' => 'date',
            'last_vet_visit' => 'datetime',
        ];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    protected static function newFactory(): HorseFactory
    {
        return HorseFactory::new();
    }
}
