<?php

declare(strict_types=1);

namespace RodeoPHP\Fields;

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
        return ['string'];
    }

    protected function meta(): array
    {
        return ['rows' => $this->rows];
    }
}
