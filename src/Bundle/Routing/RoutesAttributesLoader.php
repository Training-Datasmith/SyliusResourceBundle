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

use Sylius\Resource\Reflection\Reflection_Class_Recursive_Iterator;
use Sylius\Resource\Symfony\Routing\Factory\Attributes_Operation_Route_Factory_Interface;
use Symfony\Bundle\Framework_Bundle\Routing\Route_Loader_Interface;
use Symfony\Component\Routing\Route_Collection;
/**
 * @deprecated use Sylius\Resource\Symfony\Routing\Loader\ResourceLoader instead
 */
final class Routes_Attributes_Loader implements Route_Loader_Interface
{
    public function __construct(private array $mapping, private readonly Route_Attributes_Factory_Interface $routes_attributes_factory, private readonly Attributes_Operation_Route_Factory_Interface $attributes_operation_route_factory)
    {
    }
    public function __invoke(): Route_Collection
    {
        $route_collection = new Route_Collection();
        $paths = $this->mapping['paths'] ?? [];
        foreach (Reflection_Class_Recursive_Iterator::get_reflection_classes_from_directories($paths) as $reflection_class) {
            $class_name = $reflection_class->get_name();
            $this->routes_attributes_factory->create_route_for_class($route_collection, $class_name);
            $this->attributes_operation_route_factory->create_route_for_class($route_collection, $class_name);
        }
        return $route_collection;
    }
}