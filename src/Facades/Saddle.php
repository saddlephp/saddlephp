<?php

declare(strict_types=1);

namespace SaddlePHP\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string version()
 * @method static string|null greeting(string|null $name = null)
 * @method static string|null subgreeting()
 * @method static \SaddlePHP\Saddle resolveNonceUsing(\Closure $callback)
 * @method static string|null nonce()
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
