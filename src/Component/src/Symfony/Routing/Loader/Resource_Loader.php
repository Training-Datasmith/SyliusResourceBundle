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
namespace Sylius\Resource\Symfony\Routing\Loader;

use Sylius\Resource\Metadata\Resource\Factory\Resource_Class_List_Factory_Interface;
use Sylius\Resource\Symfony\Routing\Factory\Resource\Resource_Route_Collection_Factory_Interface;
use Symfony\Bundle\Framework_Bundle\Routing\Route_Loader_Interface;
use Symfony\Component\Routing\Route_Collection;
/**
 * @experimental
 */
final readonly class Resource_Loader implements Route_Loader_Interface
{
    public function __construct(private Resource_Class_List_Factory_Interface $resource_class_list_factory, private Resource_Route_Collection_Factory_Interface $resource_route_collection_factory)
    {
    }
    public function __invoke(): Route_Collection
    {
        $route_collection = new Route_Collection();
        $resource_classes = $this->resource_class_list_factory->create();
        /** @var class-string $class */
        foreach ($resource_classes as $class) {
            $route_collection->add_collection($this->resource_route_collection_factory->create_route_collection_for_class($class));
        }
        return $route_collection;
    }
}