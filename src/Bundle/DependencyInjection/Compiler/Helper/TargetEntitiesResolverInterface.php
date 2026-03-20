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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Helper;

interface Target_Entities_Resolver_Interface
{
    /**
     * @return array Interface to class map.
     */
    public function resolve(array $resources_configuration): array;
}