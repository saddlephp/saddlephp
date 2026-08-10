<?php

declare(strict_types=1);

namespace SaddlePHP\Support;

use BackedEnum;
use DateTimeInterface;
use JsonSerializable;
use UnitEnum;

class Csv
{
    /** Characters that make a spreadsheet treat a cell as a formula. */
    private const DANGEROUS = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Neutralize CSV formula injection: a cell whose value begins with one of
     * the dangerous characters is prefixed with a single quote so Excel,
     * Sheets, and LibreOffice render it as text instead of executing it as a
     * formula.
     *
     * Values are stringified first. A column cast to an enum, an array, a date,
     * or any Stringable used to walk straight past the is_string() guard: the
     * payload was never neutralized, and fputcsv() then fatalled on the object
     * mid-stream, after the 200 and the Content-Type were already on the wire,
     * so the browser saved a truncated file that looked like a success.
     */
    public static function neutralize(mixed $value): mixed
    {
        $value = self::stringify($value);

        if (is_string($value) && $value !== '' && in_array($value[0], self::DANGEROUS, true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Undo neutralize() so a file this package exported can be imported back
     * unchanged. Without this a round-trip turns "-12.50" into "'-12.50" and
     * "+1 555 0100" into "'+1 555 0100" -- and decimal columns arrive from
     * MySQL and Postgres as strings, so money fields hit it on every export.
     *
     * Only a quote guarding a genuinely dangerous character is removed, so a
     * value the user really did type as "'hello" survives.
     */
    public static function denormalize(mixed $value): mixed
    {
        if (! is_string($value) || $value === '' || $value[0] !== "'") {
            return $value;
        }

        $rest = substr($value, 1);

        return $rest !== '' && in_array($rest[0], self::DANGEROUS, true) ? $rest : $value;
    }

    /**
     * Strip a UTF-8 byte order mark from the first cell of a file.
     *
     * Excel, Sheets, and Numbers all write a BOM. Left in place it fuses onto
     * the first header name, so the first column silently fails to map and, if
     * that column was required, every row is skipped.
     */
    public static function stripBom(string $value): string
    {
        return str_starts_with($value, "\u{FEFF}") ? substr($value, 3) : $value;
    }

    /**
     * Reduce a resolved cell to something fputcsv() can write.
     */
    private static function stringify(mixed $value): mixed
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        // Ahead of JsonSerializable on purpose: Illuminate\Support\Stringable
        // implements both, and a cell cast with AsStringable wants its text, not
        // a JSON-quoted copy of it.
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        if ($value instanceof JsonSerializable || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }
}
