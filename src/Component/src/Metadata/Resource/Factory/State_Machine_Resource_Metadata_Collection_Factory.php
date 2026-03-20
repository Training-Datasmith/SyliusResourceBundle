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

use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Metadata\State_Machine_Aware_Operation_Interface;
use Sylius\Resource\State_Machine\State\Apply_State_Machine_Transition_Processor;
final readonly class State_Machine_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private Registry_Interface $resource_registry, private Resource_Metadata_Collection_Factory_Interface $decorated, private ?string $default_state_machine_component)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_collection_metadata = $this->decorated->create($resource_class);
        /** @var ResourceMetadata $resource */
        foreach ($resource_collection_metadata->getIterator() as $i => $resource) {
            $resource_configuration = $this->resource_registry->get($resource->get_alias() ?? '');
            $operations = $resource->get_operations() ?? new Operations();
            /** @var Operation $operation */
            foreach ($operations as $operation) {
                /** @var string $key */
                $key = $operation->get_name();
                $operations->add($key, $this->add_defaults($resource_configuration, $operation));
            }
            $resource = $resource->with_operations($operations);
            $resource_collection_metadata[$i] = $resource;
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Metadata_Interface $resource_configuration, Operation $operation): Operation
    {
        if (!$operation instanceof State_Machine_Aware_Operation_Interface) {
            return $operation;
        }
        if (null === $operation->get_state_machine_component() && method_exists($resource_configuration, 'getStateMachineComponent')) {
            $state_machine_component = $resource_configuration->get_state_machine_component() ?? $this->default_state_machine_component;
            /** @var Operation $operation */
            $operation = $operation->with_state_machine_component($state_machine_component);
        }
        if (method_exists($operation, 'getStateMachineTransition') && null !== $operation->get_state_machine_transition() && null === $operation->get_processor()) {
            return $operation->with_processor(Apply_State_Machine_Transition_Processor::class);
        }
        return $operation;
    }
}