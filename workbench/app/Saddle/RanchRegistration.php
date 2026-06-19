<?php

declare(strict_types=1);

namespace Workbench\App\Saddle;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SaddlePHP\Fields\Text;
use SaddlePHP\Tenancy\RegistersTenants;
use Workbench\App\Models\Ranch;

class RanchRegistration implements RegistersTenants
{
    public function fields(): array
    {
        return [Text::make('name')->required()->rules('max:120')];
    }

    public function register(array $validated, Authenticatable $user): Model
    {
        $ranch = Ranch::create(['name' => $validated['name']]);
        $ranch->users()->attach($user);

        return $ranch;
    }
}
