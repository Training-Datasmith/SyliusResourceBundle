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

use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
final class Register_Resource_State_Machine_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        if (!$container->has_parameter('sylius.resources')) {
            return;
        }
        /** @var array $resources */
        $resources = $container->get_parameter('sylius.resources');
        foreach ($resources as $alias => $configuration) {
            [$application_name, $resource_name] = explode('.', (string) $alias, 2);
            $state_machine_id = sprintf('%s.controller_state_machine.%s', $application_name, $resource_name);
            $state_machine_component = $configuration['state_machine_component'] ?? null;
            if (null === $state_machine_component) {
                $container->set_alias($state_machine_id, 'sylius.resource_controller.state_machine');
                continue;
            }
            $specific_state_machine_id = sprintf('sylius.resource_controller.state_machine.%s', $state_machine_component);
            if (!$container->has_definition($specific_state_machine_id)) {
                throw new \LogicException(sprintf('State machine "%s" is not available.', $state_machine_component));
            }
            $container->set_alias($state_machine_id, $specific_state_machine_id);
        }
    }
}