<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

class Markdown extends Field
{
    protected string $component = 'markdown-field';

    protected function typeRules(): array
    {
        return ['string', 'max:65535'];
    }
}
