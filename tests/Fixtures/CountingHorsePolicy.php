<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use Workbench\App\Models\Horse;
use Workbench\App\Models\User;

/**
 * Records how many times each ability is consulted, so a test can assert that
 * the index does not evaluate policies whose answers it will discard.
 */
class CountingHorsePolicy
{
    /** @var array<string, int> */
    public static array $calls = [];

    public static function reset(): void
    {
        static::$calls = [];
    }

    public static function count(string $ability): int
    {
        return static::$calls[$ability] ?? 0;
    }

    protected static function record(string $ability): bool
    {
        static::$calls[$ability] = (static::$calls[$ability] ?? 0) + 1;

        return true;
    }

    public function viewAny(User $user): bool
    {
        return static::record('viewAny');
    }

    public function create(User $user): bool
    {
        return static::record('create');
    }

    public function view(User $user, Horse $horse): bool
    {
        return static::record('view');
    }

    public function update(User $user, Horse $horse): bool
    {
        return static::record('update');
    }

    public function delete(User $user, Horse $horse): bool
    {
        return static::record('delete');
    }

    public function restore(User $user, Horse $horse): bool
    {
        return static::record('restore');
    }

    public function forceDelete(User $user, Horse $horse): bool
    {
        return static::record('forceDelete');
    }
}
