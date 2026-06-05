<?php

declare(strict_types=1);

use SaddlePHP\Tests\Tenancy\TenancyTestCase;
use SaddlePHP\Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');
uses(TenancyTestCase::class)->in('Tenancy');

if (! function_exists('findField')) {
    /**
     * Recursively locate a leaf field payload by name in a form tree.
     *
     * Walks both child-list shapes the serializer emits: container `schema`
     * arrays (Section/Grid) and `tabs[].schema` arrays (Tabs/Tab). A node is a
     * leaf field when it carries a `component` key; containers carry `layout`.
     * Returns the first matching leaf array, or null when absent.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<string, mixed>|null
     */
    function findField(array $nodes, string $name): ?array
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (isset($node['component'])) {
                if (($node['name'] ?? null) === $name) {
                    return $node;
                }

                continue;
            }

            if (isset($node['tabs']) && is_array($node['tabs'])) {
                foreach ($node['tabs'] as $tab) {
                    $found = findField($tab['schema'] ?? [], $name);

                    if ($found !== null) {
                        return $found;
                    }
                }

                continue;
            }

            if (isset($node['schema']) && is_array($node['schema'])) {
                $found = findField($node['schema'], $name);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}

if (! function_exists('flattenFields')) {
    /**
     * Recursively collect every leaf field payload from a form tree,
     * depth-first, across both `schema` and `tabs[].schema` child lists.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    function flattenFields(array $nodes): array
    {
        $leaves = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (isset($node['component'])) {
                $leaves[] = $node;

                continue;
            }

            if (isset($node['tabs']) && is_array($node['tabs'])) {
                foreach ($node['tabs'] as $tab) {
                    foreach (flattenFields($tab['schema'] ?? []) as $leaf) {
                        $leaves[] = $leaf;
                    }
                }

                continue;
            }

            if (isset($node['schema']) && is_array($node['schema'])) {
                foreach (flattenFields($node['schema']) as $leaf) {
                    $leaves[] = $leaf;
                }
            }
        }

        return $leaves;
    }
}
