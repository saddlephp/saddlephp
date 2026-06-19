<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Workbench\Database\Factories\UserFactory;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_admin'];

    protected $casts = ['is_admin' => 'boolean'];

    public function ranches(): BelongsToMany
    {
        return $this->belongsToMany(Ranch::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
