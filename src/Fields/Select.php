<?php

declare(strict_types=1);

namespace RodeoPHP\Fields;

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
}
