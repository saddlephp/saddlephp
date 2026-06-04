<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

class SelectFilter extends Filter
{
    protected string $type = 'select';

    /** @var array<array-key, string> value => label */
    protected array $options = [];

    /** @param array<array-key, string> $options */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function accepts(string $value): bool
    {
        return array_key_exists($value, $this->options);
    }

    public function apply(Builder $query, string $value): void
    {
        if ($this->accepts($value)) {
            $query->where($this->name, $value);
        }
    }

    protected function meta(): array
    {
        return [
            'options' => collect($this->options)
                ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
                ->values()->all(),
        ];
    }
}
