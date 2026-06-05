<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

class Text extends Field
{
    protected string $component = 'text-field';

    protected string $type = 'text';

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    protected function typeRules(): array
    {
        $base = match ($this->type) {
            'email' => ['email'],
            'number' => ['numeric'],
            default => ['string'],
        };

        // Bound string-ish input by default so an unbounded value can't be
        // submitted. Skipped for numeric inputs, where `max` would cap the
        // VALUE rather than the length. Appended before custom rules, so a
        // stricter author-supplied max still composes and wins.
        if ($this->type !== 'number') {
            $base[] = 'max:65535';
        }

        return $base;
    }

    protected function meta(): array
    {
        return ['type' => $this->type];
    }
}
