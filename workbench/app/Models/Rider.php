<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workbench\Database\Factories\RiderFactory;

class Rider extends Model
{
    /** @use HasFactory<RiderFactory> */
    use HasFactory;

    protected $fillable = ['name', 'ranch_id'];

    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    public function horses(): HasMany
    {
        return $this->hasMany(Horse::class);
    }

    protected static function newFactory(): RiderFactory
    {
        return RiderFactory::new();
    }
}
