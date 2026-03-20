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

use Sylius\Resource\Grid\State\Request_Grid_Provider;
use Sylius\Resource\Metadata\Grid_Aware_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Symfony\Request\State\Provider;
final readonly class Provider_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
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
                /** @var string $key */
                $key = $operation->get_name();
                $operations->add($key, $this->add_defaults($operation));
            }
            $resource = $resource->with_operations($operations);
            $resource_collection_metadata[$i] = $resource;
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Operation $operation): Operation
    {
        if (null === $operation->get_provider() && $operation instanceof Grid_Aware_Operation_Interface && null !== $operation->get_grid()) {
            $operation = $operation->with_provider(Request_Grid_Provider::class);
        }
        if (null === $operation->get_provider()) {
            return $operation->with_provider(Provider::class);
        }
        return $operation;
    }
}