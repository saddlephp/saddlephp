<?php

declare(strict_types=1);

namespace SaddlePHP\Forms\Layout;

class Section extends Layout
{
    protected ?string $description = null;

    final public function __construct(protected string $label) {}

    public static function make(string $label): static
    {
        return new static($label);
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function layout(): string
    {
        return 'section';
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return [
            'label' => $this->label,
            'description' => $this->description,
        ];
    }
}
