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

use Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event;
use Sylius\Resource\Model\Resource_Interface;
interface Event_Dispatcher_Interface
{
    public function dispatch(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event;
    /** @param mixed $resources */
    public function dispatch_multiple(string $event_name, Request_Configuration $request_configuration, $resources): Resource_Controller_Event;
    public function dispatch_pre_event(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event;
    public function dispatch_post_event(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event;
    public function dispatch_initialize_event(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event;
}