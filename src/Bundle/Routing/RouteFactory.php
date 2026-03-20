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
namespace Sylius\Bundle\Resource_Bundle\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Route_Collection;
final class Route_Factory implements Route_Factory_Interface
{
    public function create_route_collection(): Route_Collection
    {
        return new Route_Collection();
    }
    public function create_route(string $path, array $defaults = [], array $requirements = [], array $options = [], string $host = '', array $schemes = [], array $methods = [], string $condition = ''): Route
    {
        return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }
}