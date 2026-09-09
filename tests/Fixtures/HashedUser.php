<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use Workbench\App\Models\User;

/**
 * The workbench User with Laravel's `hashed` cast on the password column --
 * the combination a real panel that manages users ships, and the one that
 * turns a blank submission into a silent lockout: the cast faithfully hashes
 * '', so the row keeps a valid-looking bcrypt hash and nothing reports a fault.
 */
class HashedUser extends User
{
    protected $table = 'users';

    protected $casts = ['is_admin' => 'boolean', 'password' => 'hashed'];
}
