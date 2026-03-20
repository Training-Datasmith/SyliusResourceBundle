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
namespace Sylius\Resource\Doctrine\Common\Metadata\Resource\Factory;

use Sylius\Resource\Doctrine\Common\State\Persist_Processor;
use Sylius\Resource\Doctrine\Common\State\Remove_Processor;
use Sylius\Resource\Metadata\Delete_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Factory\Resource_Metadata_Collection_Factory_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
final readonly class Doctrine_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private Registry_Interface $resource_registry, private Resource_Metadata_Collection_Factory_Interface $decorated)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_collection_metadata = $this->decorated->create($resource_class);
        /** @var ResourceMetadata $resource */
        foreach ($resource_collection_metadata->getIterator() as $i => $resource) {
            $operations = $resource->get_operations() ?? new Operations();
            /** @var Operation $operation */
            foreach ($operations as $operation) {
                /** @var string $key */
                $key = $operation->get_name();
                $operations->add($key, $this->add_defaults($resource, $operation));
            }
            $resource = $resource->with_operations($operations);
            $resource_collection_metadata[$i] = $resource;
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Resource_Metadata $resource, Operation $operation): Operation
    {
        $metadata = $this->resource_registry->get($resource->get_alias() ?? '');
        $driver = $metadata->get_driver();
        if ($driver && str_starts_with($driver, 'doctrine/')) {
            return $operation->with_processor($this->get_processor($operation));
        }
        return $operation;
    }
    private function get_processor(Operation $operation): callable|string
    {
        if (null !== $processor = $operation->get_processor()) {
            return $processor;
        }
        if ($operation instanceof Delete_Operation_Interface) {
            return Remove_Processor::class;
        }
        return Persist_Processor::class;
    }
}