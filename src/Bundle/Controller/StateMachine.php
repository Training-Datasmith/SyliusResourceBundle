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
namespace Sylius\Bundle\Resource_Bundle\Controller;

use SM\Factory\Factory_Interface;
use Sylius\Resource\Model\Resource_Interface;
final readonly class State_Machine implements State_Machine_Interface
{
    private Factory_Interface $state_machine_factory;
    public function __construct(Factory_Interface $state_machine_factory)
    {
        $this->state_machine_factory = $state_machine_factory;
    }
    public function can(Request_Configuration $configuration, Resource_Interface $resource): bool
    {
        if (!$configuration->has_state_machine()) {
            throw new \InvalidArgumentException('State machine must be configured to apply transition, check your routing.');
        }
        $graph = $configuration->get_state_machine_graph() ?? 'default';
        /** @var string $transition */
        $transition = $configuration->get_state_machine_transition();
        return $this->state_machine_factory->get($resource, $graph)->can($transition);
    }
    public function apply(Request_Configuration $configuration, Resource_Interface $resource): void
    {
        if (!$configuration->has_state_machine()) {
            throw new \InvalidArgumentException('State machine must be configured to apply transition, check your routing.');
        }
        $graph = $configuration->get_state_machine_graph() ?? 'default';
        /** @var string $transition */
        $transition = $configuration->get_state_machine_transition();
        $this->state_machine_factory->get($resource, $graph)->apply($transition);
    }
}