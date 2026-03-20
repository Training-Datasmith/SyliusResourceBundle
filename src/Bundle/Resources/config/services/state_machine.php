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
namespace Symfony\Component\Dependency_Injection\Loader\Configurator;

use Sylius\Resource\State_Machine\Operation_State_Machine;
use Sylius\Resource\State_Machine\Operation_State_Machine_Interface;
use Sylius\Resource\Symfony\Workflow\Operation_State_Machine as SymfonyOperationStateMachine;
use Sylius\Resource\Winzou\State_Machine\Operation_State_Machine as WinzouOperationStateMachine;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.state_machine.operation', Operation_State_Machine::class)->args([tagged_locator('sylius_resource.state_machine', indexAttribute: 'key')]);
    $services->alias(Operation_State_Machine_Interface::class, 'sylius.state_machine.operation');
    $services->alias('sylius.state_machine.operation.default', 'sylius.state_machine.operation.winzou');
    $services->set('sylius.state_machine.operation.symfony', Symfony_Operation_State_Machine::class)->args([service('workflow.registry')->null_on_invalid()])->tag('sylius_resource.state_machine', ['key' => 'symfony']);
    $services->set('sylius.state_machine.operation.winzou', Winzou_Operation_State_Machine::class)->args([service('sm.factory')->null_on_invalid()])->tag('sylius_resource.state_machine', ['key' => 'winzou']);
};