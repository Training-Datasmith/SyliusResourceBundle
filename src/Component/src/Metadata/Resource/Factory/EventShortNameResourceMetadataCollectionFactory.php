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

use Sylius\Resource\Metadata\Apply_State_Machine_Transition;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Resource_Actions;
final readonly class Event_Short_Name_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
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
        if (null === $operation->get_event_short_name()) {
            $short_name = $operation instanceof Apply_State_Machine_Transition ? Resource_Actions::UPDATE : $operation->get_short_name() ?? '';
            $bulk_prefix = 'bulk_';
            if (\str_starts_with($short_name, $bulk_prefix)) {
                $short_name = substr($short_name, strlen($bulk_prefix));
            }
            $operation = $operation->with_event_short_name($short_name);
        }
        return $operation;
    }
}