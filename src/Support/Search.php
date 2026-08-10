<?php

declare(strict_types=1);

namespace SaddlePHP\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\PostgresConnection;

class Search
{
    /**
     * The case-insensitive LIKE operator for this connection's driver.
     *
     * Postgres LIKE is case-sensitive, so searching "cis" for "Cisco" returned
     * nothing at all -- the panel's search box simply appeared broken on every
     * Postgres install. MySQL folds case in its default utf8mb4_*_ci collation
     * and SQLite ASCII-folds, so both are already insensitive enough here and
     * ILIKE does not exist on either.
     */
    public static function likeOperator(Builder $query): string
    {
        return $query->getConnection() instanceof PostgresConnection ? 'ilike' : 'like';
    }

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
