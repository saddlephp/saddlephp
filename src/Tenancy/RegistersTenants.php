<?php

declare(strict_types=1);

namespace SaddlePHP\Tenancy;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

interface RegistersTenants
{
    /** @return array<int, \SaddlePHP\Fields\Field> The form fields shown on the registration page. */
    public function fields(): array;

    /**
     * Create the tenant, attach the user as a member, and return the tenant.
     *
     * @param  array<string, mixed>  $validated
     */
    public function register(array $validated, Authenticatable $user): Model;
}
