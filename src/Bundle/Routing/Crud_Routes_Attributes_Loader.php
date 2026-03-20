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

use Sylius\Component\Resource\Annotation\Sylius_Crud_Routes as LegacySyliusCrudRoutes;
use Sylius\Resource\Annotation\Sylius_Crud_Routes;
use Sylius\Resource\Reflection\Class_Reflection;
use Sylius\Resource\Reflection\Reflection_Class_Recursive_Iterator;
use Symfony\Bundle\Framework_Bundle\Routing\Route_Loader_Interface;
use Symfony\Component\Routing\Route_Collection;
use Symfony\Component\Yaml\Yaml;
final class Crud_Routes_Attributes_Loader implements Route_Loader_Interface
{
    public function __construct(private array $mapping, private readonly Resource_Loader $resource_loader)
    {
    }
    public function __invoke(): Route_Collection
    {
        $route_collection = new Route_Collection();
        $paths = $this->mapping['paths'] ?? [];
        foreach (Reflection_Class_Recursive_Iterator::get_reflection_classes_from_directories($paths) as $reflection_class) {
            $class_name = $reflection_class->get_name();
            $this->add_routes_for_sylius_crud_routes_attributes($route_collection, $class_name);
        }
        return $route_collection;
    }
    /**
     * @param class-string $className
     */
    private function add_routes_for_sylius_crud_routes_attributes(Route_Collection $route_collection, string $class_name): void
    {
        $attributes = Class_Reflection::get_class_attributes($class_name, Sylius_Crud_Routes::class);
        $attributes = array_merge($attributes, Class_Reflection::get_class_attributes($class_name, Legacy_Sylius_Crud_Routes::class));
        foreach ($attributes as $reflection_attribute) {
            $resource = Yaml::dump($reflection_attribute->get_arguments());
            $resource_route_collection = $this->resource_loader->load($resource);
            $route_collection->add_collection($resource_route_collection);
        }
    }
}