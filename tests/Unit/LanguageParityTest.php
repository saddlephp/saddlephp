<?php

declare(strict_types=1);

/**
 * Every shipped locale must translate exactly the same keys as English, with
 * every interpolation placeholder ({brace} for vue-i18n, :colon for Laravel)
 * preserved. This guards against a key being added to en/panel.php without a
 * matching translation in the others.
 */
function flattenPanel(array $messages, string $prefix = ''): array
{
    $flat = [];
    foreach ($messages as $key => $value) {
        $dotted = $prefix === '' ? $key : "$prefix.$key";
        if (is_array($value)) {
            $flat += flattenPanel($value, $dotted);
        } else {
            $flat[$dotted] = $value;
        }
    }

    return $flat;
}

function panelPlaceholders(string $value): array
{
    preg_match_all('/\{[a-z_]+\}|:[a-z_]+/', $value, $matches);

    return $matches[0];
}

$langPath = dirname(__DIR__, 2).'/lang';
$english = flattenPanel(require "$langPath/en/panel.php");

$locales = array_values(array_filter(
    array_map('basename', glob("$langPath/*", GLOB_ONLYDIR)),
    fn (string $locale) => $locale !== 'en',
));

it('ships at least the locales the marketing site offers', function () use ($locales) {
    // en + these 13 = the 14 languages SaddlePHP standardises on.
    $expected = ['ar', 'de', 'es', 'fr', 'it', 'ja', 'ko', 'nl', 'pl', 'pt_BR', 'ru', 'tr', 'zh_CN'];

    expect(array_values(array_intersect($expected, $locales)))->toEqualCanonicalizing($expected);
});

it('translates the same keys as English with placeholders intact', function (string $locale) use ($langPath, $english) {
    $translated = flattenPanel(require "$langPath/$locale/panel.php");

    expect(array_keys($translated))->toEqualCanonicalizing(array_keys($english));

    foreach ($english as $key => $source) {
        foreach (panelPlaceholders($source) as $placeholder) {
            expect($translated[$key])->toContain($placeholder);
        }
    }
})->with($locales);
