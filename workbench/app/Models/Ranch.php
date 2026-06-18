<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workbench\Database\Factories\RanchFactory;

class Ranch extends Model
{
    /** @use HasFactory<RanchFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function horses(): HasMany
    {
        return $this->hasMany(Horse::class);
    }

    protected static function newFactory(): RanchFactory
    {
        return RanchFactory::new();
    }
}
