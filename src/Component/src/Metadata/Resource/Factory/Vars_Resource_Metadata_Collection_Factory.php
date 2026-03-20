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

use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
final readonly class Vars_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private Resource_Metadata_Collection_Factory_Interface $decorated)
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
                if (!$operation instanceof Http_Operation) {
                    continue;
                }
                /** @var string $key */
                $key = $operation->get_name();
                $operations->add($key, $this->add_defaults($resource, $operation));
            }
            $resource = $resource->with_operations($operations);
            $resource_collection_metadata[$i] = $resource;
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Resource_Metadata $resource, Http_Operation $operation): \Sylius\Resource\Metadata\Http_Operation
    {
        if (null === $resource_vars = $resource->get_vars()) {
            return $operation;
        }
        return $operation->with_vars(\array_merge($resource_vars, $operation->get_vars() ?? []));
    }
}