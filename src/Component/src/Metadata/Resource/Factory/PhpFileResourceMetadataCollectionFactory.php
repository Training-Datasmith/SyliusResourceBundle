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

use Sylius\Resource\Metadata\Extractor\Resource_Extractor_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Symfony\Routing\Factory\Route_Name\Operation_Route_Name_Factory_Interface;
/**
 * @experimental
 */
final readonly class Php_File_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    use Operation_Defaults_Trait;
    public function __construct(private Registry_Interface $resource_registry, private Operation_Route_Name_Factory_Interface $operation_route_name_factory, private Resource_Extractor_Interface $phpfile_resource_metadata_extractor, private ?Resource_Metadata_Collection_Factory_Interface $decorated = null)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_metadata_collection = new Resource_Metadata_Collection();
        if ($this->decorated) {
            $resource_metadata_collection = $this->decorated->create($resource_class);
        }
        foreach ($this->phpfile_resource_metadata_extractor->get_resources() as $resource) {
            if ($resource_class !== $resource->get_class()) {
                continue;
            }
            $resource_alias = $resource->get_alias();
            if (null !== $resource_alias) {
                $resource_configuration = $this->resource_registry->get($resource_alias);
            } else {
                $resource_configuration = $this->resource_registry->get_by_class($resource_class);
            }
            $resource = $this->get_resource_with_defaults($resource_class, $resource, $resource_configuration);
            $operations = [];
            /** @var Operation $operation */
            foreach ($resource->get_operations() ?? new Operations() as $operation) {
                [$key, $operation] = $this->get_operation_with_defaults($operation, $resource, $this->operation_route_name_factory, $this->resource_registry);
                $operations[$key] = $operation;
            }
            if ($operations) {
                $resource = $resource->with_operations(new Operations($operations));
            }
            $resource_metadata_collection[] = $resource;
        }
        return $resource_metadata_collection;
    }
}