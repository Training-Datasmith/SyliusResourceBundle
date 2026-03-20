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
namespace Sylius\Resource\Symfony\Routing\Factory\Resource;

use Symfony\Component\Routing\Route_Collection;
/**
 * @experimental
 */
interface Resource_Route_Collection_Factory_Interface
{
    /** @param class-string $className */
    public function create_route_collection_for_class(string $class_name): Route_Collection;
}