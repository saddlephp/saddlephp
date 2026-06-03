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
        return match ($this->type) {
            'email' => ['email'],
            'number' => ['numeric'],
            default => ['string'],
        };
    }

    protected function meta(): array
    {
        return ['type' => $this->type];
    }
}
