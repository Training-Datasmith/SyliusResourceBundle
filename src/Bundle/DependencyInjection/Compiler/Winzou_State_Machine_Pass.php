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

use SM\Callback\Callback_Factory_Interface;
use SM\Callback\Cascade_Transition_Callback;
use SM\Factory\Factory_Interface;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use winzou\Bundle\State_Machine_Bundle\Winzou_State_Machine_Bundle;
/**
 * Marks WinzouStateMachineBundle's services as public for compatibility with both Symfony 3.4 and 4.0+.
 * Aliases FQCN-based services for backwards compatibility of Winzou/StateMachineBundle 0.4 with 0.3
 *
 * @see https://github.com/winzou/StateMachineBundle/pull/44
 * @see https://github.com/winzou/StateMachineBundle/commit/f515c9302783ef2575570d33b20aefa1eb265afb
 */
final class Winzou_State_Machine_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        /** @var array $bundles */
        $bundles = $container->get_parameter('kernel.bundles');
        $winzou_state_machine_enabled = in_array(Winzou_State_Machine_Bundle::class, $bundles, true);
        if (!$winzou_state_machine_enabled) {
            return;
        }
        if ($container->has_definition('sm.factory') && !$container->has_definition(Factory_Interface::class)) {
            $container->set_alias(Factory_Interface::class, 'sm.factory');
        } else {
            $container->set_alias('sm.factory', Factory_Interface::class);
        }
        if ($container->has_definition('sm.callback_factory') && !$container->has_definition(Callback_Factory_Interface::class)) {
            $container->set_alias(Callback_Factory_Interface::class, 'sm.callback_factory');
        } else {
            $container->set_alias('sm.callback_factory', Callback_Factory_Interface::class);
        }
        if ($container->has_definition('sm.callback.cascade_transition') && !$container->has_definition(Cascade_Transition_Callback::class)) {
            $container->set_alias(Cascade_Transition_Callback::class, 'sm.callback.cascade_transition');
        } else {
            $container->set_alias('sm.callback.cascade_transition', Cascade_Transition_Callback::class);
        }
        $services = ['sm.factory', 'sm.callback_factory', 'sm.callback.cascade_transition', Factory_Interface::class, Callback_Factory_Interface::class, Cascade_Transition_Callback::class];
        foreach ($services as $id) {
            if ($container->has_alias($id)) {
                $container->get_alias($id)->set_public(true);
            }
            if ($container->has_definition($id)) {
                $container->get_definition($id)->set_public(true);
            }
        }
    }
}