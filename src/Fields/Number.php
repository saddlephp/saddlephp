<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

class Number extends Field
{
    protected string $component = 'number-field';

    protected float|int|null $min = null;

    protected float|int|null $max = null;

    protected float|int|null $step = null;

    protected bool $integer = false;

    public function min(float|int $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function max(float|int $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function step(float|int $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function integer(bool $integer = true): static
    {
        $this->integer = $integer;

        return $this;
    }

    protected function typeRules(): array
    {
        $rules = [$this->integer ? 'integer' : 'numeric'];

        if ($this->min !== null) {
            $rules[] = 'min:'.$this->min;
        }

        if ($this->max !== null) {
            $rules[] = 'max:'.$this->max;
        }

        return $rules;
    }

    protected function meta(): array
    {
        return [
            'type' => 'number',
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
        ];
    }
}
