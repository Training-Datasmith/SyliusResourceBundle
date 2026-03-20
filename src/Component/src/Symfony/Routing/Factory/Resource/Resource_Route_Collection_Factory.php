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

use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Factory\Resource_Metadata_Collection_Factory_Interface;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Symfony\Routing\Factory\Operation_Route_Factory_Interface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Route_Collection;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Resource_Route_Collection_Factory implements Resource_Route_Collection_Factory_Interface
{
    public function __construct(private Operation_Route_Factory_Interface $operation_route_factory, private Resource_Metadata_Collection_Factory_Interface $resource_metadata_factory, private Registry_Interface $resource_registry)
    {
    }
    public function create_route_collection_for_class(string $class_name): Route_Collection
    {
        $route_collection = new Route_Collection();
        $resource_metadata = $this->resource_metadata_factory->create($class_name);
        /** @var ResourceMetadata $resource */
        foreach ($resource_metadata->getIterator() as $resource) {
            $this->create_routes_for_resource($route_collection, $resource);
        }
        return $route_collection;
    }
    private function create_routes_for_resource(Route_Collection $route_collection, Resource_Metadata $resource): void
    {
        foreach ($resource->get_operations() ?? new Operations() as $operation) {
            if (!$operation instanceof Http_Operation) {
                continue;
            }
            $this->add_route_for_operation($route_collection, $resource, $operation);
        }
    }
    private function add_route_for_operation(Route_Collection $route_collection, Resource_Metadata $resource, Http_Operation $operation): void
    {
        $alias = $resource->get_alias();
        Assert::not_null($alias, sprintf('Resource of %s has no alias.', $resource->get_class() ?? ''));
        $metadata = $this->resource_registry->get($alias);
        $route_name = $operation->get_route_name();
        Assert::not_null($route_name, sprintf('Operation %s of %s has no route name. Please define one.', $operation::class, $alias));
        $route = $this->create_route($metadata, $resource, $operation);
        $route_collection->add($route_name, $route, $operation->get_route_priority() ?? 0);
    }
    private function create_route(Metadata_Interface $metadata, Resource_Metadata $resource, Http_Operation $operation): Route
    {
        return $this->operation_route_factory->create($metadata, $resource, $operation);
    }
}