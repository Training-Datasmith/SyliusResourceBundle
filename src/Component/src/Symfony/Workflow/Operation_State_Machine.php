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
namespace Sylius\Resource\Symfony\Workflow;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\State_Machine_Aware_Operation_Interface;
use Sylius\Resource\State_Machine\Operation_State_Machine_Interface;
use Symfony\Component\Workflow\Registry;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Operation_State_Machine implements Operation_State_Machine_Interface
{
    public function __construct(private ?Registry $registry = null)
    {
    }
    public function can(object $data, Operation $operation, Context $context): bool
    {
        Assert::is_instance_of($operation, State_Machine_Aware_Operation_Interface::class);
        $transition = $operation->get_state_machine_transition() ?? null;
        Assert::not_null($transition, sprintf('No State machine transition was found on operation "%s".', $operation->get_name() ?? ''));
        $graph = $operation->get_state_machine_graph() ?? null;
        return $this->get_registry()->get($data, $graph)->can($data, $transition);
    }
    public function apply(object $data, Operation $operation, Context $context): void
    {
        Assert::is_instance_of($operation, State_Machine_Aware_Operation_Interface::class);
        $transition = $operation->get_state_machine_transition() ?? null;
        Assert::not_null($transition, sprintf('No State machine transition was found on operation "%s".', $operation->get_name() ?? ''));
        $graph = $operation->get_state_machine_graph() ?? null;
        $this->get_registry()->get($data, $graph)->apply($data, $transition);
    }
    private function get_registry(): Registry
    {
        if (null === $this->registry) {
            throw new \LogicException('You can not use the "state-machine" if Symfony workflow is not available. Try running "composer require symfony/workflow".');
        }
        return $this->registry;
    }
}