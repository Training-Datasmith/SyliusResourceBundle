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
namespace Sylius\Resource\Metadata\Resource\Factory;

use Sylius\Resource\Metadata\As_Resource;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Reflection\Class_Reflection;
use Sylius\Resource\Symfony\Routing\Factory\Route_Name\Operation_Route_Name_Factory_Interface;
final readonly class Attributes_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    use Operation_Defaults_Trait;
    public function __construct(private Registry_Interface $resource_registry, private Operation_Route_Name_Factory_Interface $operation_route_name_factory)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_metadata_collection = new Resource_Metadata_Collection();
        $attributes = Class_Reflection::get_class_attributes($resource_class);
        foreach ($this->build_resource_operations($attributes, $resource_class) as $resource) {
            $resource_metadata_collection[] = $resource;
        }
        return $resource_metadata_collection;
    }
    /**
     * @param \ReflectionAttribute[] $attributes
     *
     * @return ResourceMetadata[]
     */
    private function build_resource_operations(array $attributes, string $resource_class): array
    {
        /** @var array<int, ResourceMetadata> $resources */
        $resources = [];
        $index = -1;
        foreach ($attributes as $attribute) {
            if (is_a($attribute->get_name(), As_Resource::class, true)) {
                /** @var AsResource $resourceAttribute */
                $resource_attribute = $attribute->new_instance();
                $resource = $resource_attribute->to_metadata();
                $resource_alias = $resource->get_alias();
                if (null !== $resource_alias) {
                    $resource_configuration = $this->resource_registry->get($resource->get_alias() ?? '');
                } else {
                    $resource_configuration = $this->resource_registry->get_by_class($resource_class);
                }
                $resource = $this->get_resource_with_defaults($resource_class, $resource, $resource_configuration);
                $resources[++$index] = $resource;
                $operations = [];
                /** @var Operation $operation */
                foreach ($resource->get_operations() ?? new Operations() as $operation) {
                    [$key, $operation] = $this->get_operation_with_defaults($operation, $resources[$index], $this->operation_route_name_factory, $this->resource_registry);
                    $operations[$key] = $operation;
                }
                if ($operations) {
                    $resources[$index] = $resources[$index]->with_operations(new Operations($operations));
                }
                continue;
            }
            if (null === ($resources[$index] ?? null)) {
                try {
                    $resource_configuration = $this->resource_registry->get_by_class($resource_class);
                    $resource = new Resource_Metadata($resource_configuration->get_alias());
                    $resource = $this->get_resource_with_defaults($resource_class, $resource, $resource_configuration);
                    $resources[++$index] = $resource;
                } catch (\InvalidArgumentException) {
                }
            }
            if (!is_subclass_of($attribute->get_name(), Operation::class)) {
                continue;
            }
            /** @var Operation $operationAttribute */
            $operation_attribute = $attribute->new_instance();
            [$key, $operation] = $this->get_operation_with_defaults($operation_attribute, $resources[$index], $this->operation_route_name_factory, $this->resource_registry);
            $operations = $resources[$index]->get_operations() ?? new Operations();
            $resources[$index] = $resources[$index]->with_operations($operations);
            $resources[$index] = $resources[$index]->with_operations($operations->add($key, $operation));
        }
        return $resources;
    }
}