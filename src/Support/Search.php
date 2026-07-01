<?php

declare(strict_types=1);

namespace SaddlePHP\Support;

class Search
{
    /**
     * Escape a user-supplied term for use inside a LIKE pattern so that the
     * wildcards `%` and `_` (and the escape character `\`) are matched
     * literally instead of letting a search for "50%" match every row.
     *
     * Callers wrap the result with their own `%…%` wildcards.
     */
    public static function escapeLike(string $term): string
    {
        return addcslashes($term, '\\%_');
    }
}
