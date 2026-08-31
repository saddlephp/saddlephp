<?php

declare(strict_types=1);

namespace SaddlePHP\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\PostgresConnection;

class Search
{
    /**
     * The LIKE escape character.
     *
     * Deliberately not a backslash. The escape character has to survive being
     * written into the SQL text as a string literal, and `ESCAPE '\'` is a
     * syntax error on MySQL, where a backslash escapes the closing quote and
     * leaves the literal unterminated. `!` is an ordinary character in every
     * driver's string literals, so `ESCAPE '!'` needs no per-driver quoting.
     */
    public const ESCAPE = '!';

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
     * wildcards `%` and `_` (and the escape character itself) are matched
     * literally instead of letting a search for "50%" match every row.
     *
     * Escaping alone is not enough: the pattern only means anything paired with
     * an ESCAPE clause, which is what `condition()` compiles. SQLite has no
     * default LIKE escape character at all, so without that clause the escape
     * character is matched literally and any term containing `%` or `_`
     * returned zero rows.
     *
     * Callers wrap the result with their own `%…%` wildcards.
     */
    public static function escapeLike(string $term): string
    {
        return str_replace(
            [self::ESCAPE, '%', '_'],
            [self::ESCAPE.self::ESCAPE, self::ESCAPE.'%', self::ESCAPE.'_'],
            $term,
        );
    }

    /**
     * A `column LIKE ? ESCAPE '!'` fragment for the given column, using the
     * driver's case-insensitive operator. The pattern is left as a bound
     * parameter for the caller to supply.
     *
     * The column is run through the connection's grammar so identifiers are
     * quoted the same way the query builder would quote them.
     */
    public static function condition(Builder $query, string $column): string
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $wrapped = $query->getGrammar()->wrap($column);

        return $wrapped.' '.self::likeOperator($query)." ? escape '".self::ESCAPE."'";
    }
}
