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

use Sylius\Resource\Metadata\Mutator\Operation_Mutator_Collection_Interface;
use Sylius\Resource\Metadata\Mutator\Resource_Mutator_Collection_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
final readonly class Mutator_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private Resource_Mutator_Collection_Interface $resource_mutators, private Operation_Mutator_Collection_Interface $operation_mutators, private ?Resource_Metadata_Collection_Factory_Interface $decorated = null)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_metadata_collection = new Resource_Metadata_Collection();
        if ($this->decorated) {
            $resource_metadata_collection = $this->decorated->create($resource_class);
        }
        $new_metadata_collection = new Resource_Metadata_Collection();
        /** @var ResourceMetadata $resource */
        foreach ($resource_metadata_collection as $resource) {
            $resource = $this->mutate_resource($resource, $resource_class);
            $operations = $this->mutate_operations($resource->get_operations() ?? new Operations());
            $resource = $resource->with_operations($operations);
            $new_metadata_collection[] = $resource;
        }
        return $new_metadata_collection;
    }
    private function mutate_resource(Resource_Metadata $resource, string $resource_class): Resource_Metadata
    {
        foreach ($this->resource_mutators->get($resource_class) as $mutator) {
            $resource = $mutator($resource);
        }
        return $resource;
    }
    /**
     * @template T of Operation
     *
     * @param Operations<T> $operations
     *
     * @return Operations<T>
     */
    private function mutate_operations(Operations $operations): Operations
    {
        $new_operations = new Operations();
        foreach ($operations as $key => $operation) {
            foreach ($this->operation_mutators->get($key) as $mutator) {
                $operation = $mutator($operation);
            }
            $new_operations->add($key, $operation);
        }
        return $new_operations;
    }
}