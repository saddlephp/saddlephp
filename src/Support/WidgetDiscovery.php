<?php

declare(strict_types=1);

namespace SaddlePHP\Support;

use ReflectionClass;
use SaddlePHP\Widgets\Widget;

class WidgetDiscovery
{
    /** @return array<int, class-string<Widget>> */
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

            if ($reflection->isSubclassOf(Widget::class) && ! $reflection->isAbstract()) {
                $classes[] = $class;
            }
        }

        // Order by the widget's $sort, then class name as a stable tiebreak.
        usort($classes, fn (string $a, string $b) => [$a::$sort, $a] <=> [$b::$sort, $b]);

        return $classes;
    }
}
