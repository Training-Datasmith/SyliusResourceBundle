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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler;

use Sylius\Bundle\Resource_Bundle\Controller\State_Machine;
use Sylius\Bundle\Resource_Bundle\Controller\Workflow;
use Sylius\Bundle\Resource_Bundle\Resource_Bundle_Interface;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Reference;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;
use winzou\Bundle\State_Machine_Bundle\Winzou_State_Machine_Bundle;
final class Register_State_Machine_Pass implements Compiler_Pass_Interface
{
    /**
     * @inheritdoc
     */
    public function process(Container_Builder $container): void
    {
        /** @var array $settings */
        $settings = $container->get_parameter('sylius.resource.settings');
        $state_machine = $settings['state_machine_component'];
        $container->set_parameter('sylius.state_machine_component.default', null);
        $this->register_winzou_state_machine($container);
        $this->register_symfony_workflow_state_machine($container);
        $this->register_winzou_state_machine($container);
        $this->register_symfony_workflow_state_machine($container);
        if (null !== $state_machine) {
            $this->set_state_machine($container, $state_machine);
            return;
        }
        // No state machine enabled
        if (!$this->is_symfony_workflow_enabled($container) && !$this->is_winzou_state_machine_enabled($container)) {
            return;
        }
        if ($this->is_winzou_state_machine_enabled($container)) {
            $this->set_state_machine($container, Resource_Bundle_Interface::STATE_MACHINE_WINZOU);
            return;
        }
        $this->set_state_machine($container, Resource_Bundle_Interface::STATE_MACHINE_SYMFONY);
    }
    private function set_state_machine(Container_Builder $container, string $state_machine): void
    {
        if (Resource_Bundle_Interface::STATE_MACHINE_SYMFONY === $state_machine) {
            $this->set_symfony_workflow_as_state_machine($container);
            return;
        }
        if (Resource_Bundle_Interface::STATE_MACHINE_WINZOU === $state_machine) {
            $this->set_winzou_as_state_machine($container);
            return;
        }
    }
    private function set_winzou_as_state_machine(Container_Builder $container): void
    {
        if (!$this->is_winzou_state_machine_enabled($container)) {
            throw new \LogicException('You can not use "Winzou" for your state machine if it is not available. Try running "composer require winzou/state-machine-bundle".');
        }
        $container->set_parameter('sylius.state_machine_component.default', 'winzou');
        $state_machine_definition = $container->register('sylius.resource_controller.state_machine', State_Machine::class);
        $state_machine_definition->set_public(false);
        $state_machine_definition->add_argument(new Reference('sm.factory'));
        $container->set_alias('sylius.state_machine.operation.default', 'sylius.state_machine.operation.winzou');
    }
    private function register_winzou_state_machine(Container_Builder $container): void
    {
        if (!$this->is_winzou_state_machine_enabled($container)) {
            return;
        }
        $state_machine_definition = $container->register('sylius.resource_controller.state_machine.winzou', State_Machine::class);
        $state_machine_definition->set_public(false);
        $state_machine_definition->add_argument(new Reference('sm.factory'));
    }
    private function register_symfony_workflow_state_machine(Container_Builder $container): void
    {
        if (!$this->is_symfony_workflow_enabled($container)) {
            return;
        }
        $state_machine_definition = $container->register('sylius.resource_controller.state_machine.symfony', Workflow::class);
        $state_machine_definition->set_public(false);
        $state_machine_definition->add_argument(new Reference('workflow.registry'));
    }
    private function set_symfony_workflow_as_state_machine(Container_Builder $container): void
    {
        if (!$this->is_symfony_workflow_enabled($container)) {
            if (class_exists(Symfony_Workflow::class)) {
                throw new \LogicException('You can not use "Symfony" for your state machine if it is not enabled on framework bundle.');
            }
            throw new \LogicException('You can not use "Symfony" for your state machine if it is not available. Try running "composer require symfony/workflow".');
        }
        $container->set_parameter('sylius.state_machine_component.default', 'symfony');
        $state_machine_definition = $container->register('sylius.resource_controller.state_machine', Workflow::class);
        $state_machine_definition->set_public(false);
        $state_machine_definition->add_argument(new Reference('workflow.registry'));
        $container->set_alias('sylius.state_machine.operation.default', 'sylius.state_machine.operation.symfony');
    }
    private function is_symfony_workflow_enabled(Container_Builder $container): bool
    {
        if ($container->has_definition('workflow.registry')) {
            return true;
        }
        return (bool) $container->has_alias('workflow.registry');
    }
    private function is_winzou_state_machine_enabled(Container_Builder $container): bool
    {
        /** @var array $bundles */
        $bundles = $container->get_parameter('kernel.bundles');
        return in_array(Winzou_State_Machine_Bundle::class, $bundles, true);
    }
}