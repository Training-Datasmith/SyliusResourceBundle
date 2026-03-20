<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Resource\Metadata\Mutator;

use Sylius\Resource\Metadata\Resource_Mutator_Interface;
/**
 * @internal
 */
final class Resource_Mutator_Collection implements Resource_Mutator_Collection_Interface
{
    /** @var array<string, list<ResourceMutatorInterface>> */
    private array $mutators = [];
    public function add(string $resource_class, Resource_Mutator_Interface $mutator): void
    {
        $this->mutators[$resource_class][] = $mutator;
    }
    public function get(string $id): array
    {
        return $this->mutators[$id] ?? [];
    }
    public function has(string $id): bool
    {
        return isset($this->mutators[$id]);
    }
}