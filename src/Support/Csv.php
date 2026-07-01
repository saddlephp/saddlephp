<?php

declare(strict_types=1);

namespace SaddlePHP\Support;

class Csv
{
    /** Characters that make a spreadsheet treat a cell as a formula. */
    private const DANGEROUS = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Neutralize CSV formula injection: a cell whose value begins with one of
     * the dangerous characters is prefixed with a single quote so Excel,
     * Sheets, and LibreOffice render it as text instead of executing it as a
     * formula. Non-string values (numbers, null) pass through unchanged.
     */
    public static function neutralize(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], self::DANGEROUS, true)) {
            return "'".$value;
        }

        return $value;
    }
}
