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

use Sylius\Resource\Metadata\Factory_Aware_Operation_Interface;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
final readonly class Factory_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private Registry_Interface $resource_registry, private Resource_Metadata_Collection_Factory_Interface $decorated)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_collection_metadata = $this->decorated->create($resource_class);
        /** @var ResourceMetadata $resource */
        foreach ($resource_collection_metadata->getIterator() as $i => $resource) {
            $resource_configuration = $this->resource_registry->get($resource->get_alias() ?? '');
            $operations = $resource->get_operations() ?? new Operations();
            /** @var Operation|(Operation&FactoryAwareOperationInterface) $operation */
            foreach ($operations as $operation) {
                if (!$operation instanceof Factory_Aware_Operation_Interface) {
                    continue;
                }
                /** @var string $key */
                $key = $operation->get_name();
                /** @var Operation&FactoryAwareOperationInterface $operation */
                $operation = $this->add_defaults($resource_configuration, $operation);
                $operations->add($key, $operation);
            }
            $resource = $resource->with_operations($operations);
            $resource_collection_metadata[$i] = $resource;
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Metadata_Interface $resource_configuration, Factory_Aware_Operation_Interface $operation): Factory_Aware_Operation_Interface
    {
        if (null === $operation->get_factory() && str_starts_with($resource_configuration->get_driver() ?: '', 'doctrine')) {
            $operation = $operation->with_factory($resource_configuration->get_service_id('factory'));
        }
        if (null === $operation->get_factory_method()) {
            return $operation->with_factory_method('createNew');
        }
        return $operation;
    }
}