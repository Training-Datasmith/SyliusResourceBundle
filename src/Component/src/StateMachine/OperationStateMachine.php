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
namespace Sylius\Resource\State_Machine;

use Psr\Container\Container_Interface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\State_Machine_Aware_Operation_Interface;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Operation_State_Machine implements Operation_State_Machine_Interface
{
    public function __construct(private Container_Interface $locator)
    {
    }
    public function can(object $data, Operation $operation, Context $context): bool
    {
        $state_machine = $this->get_state_machine($operation);
        return $state_machine?->can($data, $operation, $context) ?? false;
    }
    public function apply(object $data, Operation $operation, Context $context): void
    {
        $state_machine = $this->get_state_machine($operation);
        $state_machine?->apply($data, $operation, $context);
    }
    private function get_state_machine(Operation $operation): ?Operation_State_Machine_Interface
    {
        Assert::is_instance_of($operation, State_Machine_Aware_Operation_Interface::class);
        $state_machine = $operation->get_state_machine_component();
        if (null === $state_machine) {
            return null;
        }
        if (!$this->locator->has($state_machine)) {
            throw new \RuntimeException(sprintf('State machine "%s" not found on operation "%s"', $state_machine, $operation->get_name() ?? ''));
        }
        $state_machine_instance = $this->locator->get($state_machine);
        Assert::is_instance_of($state_machine_instance, Operation_State_Machine_Interface::class);
        return $state_machine_instance;
    }
}