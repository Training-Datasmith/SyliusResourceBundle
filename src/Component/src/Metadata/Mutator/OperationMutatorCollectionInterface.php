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

use Psr\Container\Container_Interface;
use Sylius\Resource\Metadata\Operation_Mutator_Interface;
/**
 * Collection of Operation mutators to mutate Operation metadata.
 *
 * @experimental
 */
interface Operation_Mutator_Collection_Interface extends Container_Interface
{
    /**
     * @return list<OperationMutatorInterface>
     */
    public function get(string $id): array;
}