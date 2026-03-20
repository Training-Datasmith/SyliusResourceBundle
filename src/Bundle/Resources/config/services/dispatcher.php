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

use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Dispatcher;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Dispatcher_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Handler;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Handler_Interface;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.dispatcher.operation', Operation_Event_Dispatcher::class)->args([service('event_dispatcher')]);
    $services->alias(Operation_Event_Dispatcher_Interface::class, 'sylius.dispatcher.operation');
    $services->set('sylius.event_handler.operation', Operation_Event_Handler::class)->args([service('sylius.routing.redirect_handler'), service('sylius.helper.flash')]);
    $services->alias(Operation_Event_Handler_Interface::class, 'sylius.event_handler.operation');
};