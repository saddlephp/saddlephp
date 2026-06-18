<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Select extends Field
{
    protected string $component = 'select-field';

    /** @var array<int, array{value: string|int, label: string}> */
    protected array $options = [];

    /** @param array<int|string, string>|class-string<\BackedEnum> $options */
    public function options(array|string $options): static
    {
        if (is_string($options)) {
            if (! is_subclass_of($options, \BackedEnum::class)) {
                throw new \InvalidArgumentException(
                    'Select options expect an array or a backed enum class-string.',
                );
            }

            $this->options = collect($options::cases())
                ->map(fn (\BackedEnum $case) => ['value' => $case->value, 'label' => $case->name])
                ->values()->all();

            return $this;
        }

        $this->options = array_is_list($options)
            ? collect($options)->map(fn ($option) => ['value' => $option, 'label' => (string) $option])->values()->all()
            : collect($options)->map(fn ($label, $key) => ['value' => $key, 'label' => $label])->values()->all();

        return $this;
    }

    protected function typeRules(): array
    {
        return [Rule::in(array_column($this->options, 'value'))];
    }

    protected function meta(): array
    {
        return ['options' => $this->options];
    }

    protected function displayValue(?Model $record): mixed
    {
        if ($record === null) {
            return null;
        }

        $value = $this->resolve($record);

        if ($value === null) {
            return null;
        }

        return collect($this->options)->firstWhere('value', $value)['label'] ?? (string) $value;
    }
}
