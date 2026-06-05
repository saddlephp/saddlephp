<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

class Textarea extends Field
{
    protected string $component = 'textarea-field';

    protected int $rows = 4;

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    protected function typeRules(): array
    {
        // Bound the input by default so an unbounded string can't be submitted.
        // Appended before custom rules, so a stricter author-supplied max still
        // composes and wins for longer values.
        return ['string', 'max:65535'];
    }

    protected function meta(): array
    {
        return ['rows' => $this->rows];
    }
}
