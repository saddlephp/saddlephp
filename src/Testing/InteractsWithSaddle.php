<?php

declare(strict_types=1);

namespace SaddlePHP\Testing;

use PHPUnit\Framework\Assert;
use SaddlePHP\Saddle;

trait InteractsWithSaddle
{
    /**
     * @param  class-string<\SaddlePHP\Resource>  $resource
     * @return array<int, array<string, mixed>>
     */
    protected function saddleForm(string $resource): array
    {
        return $resource::makeForm()->toInertia();
    }

    /**
     * @param  class-string<\SaddlePHP\Resource>  $resource
     * @return array<int, array<string, mixed>>
     */
    protected function saddleTable(string $resource): array
    {
        return $resource::makeTable()->toInertia();
    }

    protected function assertResourceRegistered(string $uriKey): void
    {
        Assert::assertNotNull(
            app(Saddle::class)->resourceFor($uriKey),
            "No Saddle resource registered for uri key [{$uriKey}].",
        );
    }

    /** @param class-string<\SaddlePHP\Resource> $resource */
    protected function assertResourceHasField(string $resource, string $name): void
    {
        Assert::assertNotNull(
            $this->findSaddleField($this->saddleForm($resource), $name),
            "Resource [{$resource}] has no field [{$name}].",
        );
    }

    /** @param class-string<\SaddlePHP\Resource> $resource */
    protected function assertResourceMissingField(string $resource, string $name): void
    {
        Assert::assertNull(
            $this->findSaddleField($this->saddleForm($resource), $name),
            "Resource [{$resource}] unexpectedly has field [{$name}].",
        );
    }

    /** @param class-string<\SaddlePHP\Resource> $resource */
    protected function assertResourceHasColumn(string $resource, string $name): void
    {
        $names = array_column($this->saddleTable($resource), 'name');

        Assert::assertContains($name, $names, "Resource [{$resource}] has no column [{$name}].");
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<string, mixed>|null
     */
    private function findSaddleField(array $nodes, string $name): ?array
    {
        foreach ($nodes as $node) {
            if (isset($node['component'])) {
                if (($node['name'] ?? null) === $name) {
                    return $node;
                }

                continue;
            }

            if (isset($node['tabs']) && is_array($node['tabs'])) {
                foreach ($node['tabs'] as $tab) {
                    if ($found = $this->findSaddleField($tab['schema'] ?? [], $name)) {
                        return $found;
                    }
                }

                continue;
            }

            if (isset($node['schema']) && is_array($node['schema'])) {
                if ($found = $this->findSaddleField($node['schema'], $name)) {
                    return $found;
                }
            }
        }

        return null;
    }
}
