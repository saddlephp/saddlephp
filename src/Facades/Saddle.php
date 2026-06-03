<?php

declare(strict_types=1);

namespace SaddlePHP\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string version()
 * @method static string greeting()
 *
 * @see \SaddlePHP\Saddle
 */
class Saddle extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SaddlePHP\Saddle::class;
    }
}
