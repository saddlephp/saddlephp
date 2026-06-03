<?php

declare(strict_types=1);

namespace RodeoPHP\Support;

use ReflectionClass;
use RodeoPHP\Resource;

class ResourceDiscovery
{
    /** @return array<int, class-string<resource>> */
    public static function in(string $directory, string $namespace): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $classes = [];

        foreach (glob($directory.'/*.php') ?: [] as $file) {
            $class = rtrim($namespace, '\\').'\\'.pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isSubclassOf(Resource::class) && ! $reflection->isAbstract()) {
                $classes[] = $class;
            }
        }

        sort($classes);

        return $classes;
    }
}
