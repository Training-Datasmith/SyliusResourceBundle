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
use Symfony\Contracts\Event_Dispatcher\Event_Dispatcher_Interface as SymfonyEventDispatcherInterface;
final readonly class Event_Dispatcher implements Event_Dispatcher_Interface
{
    public function __construct(private Symfony_Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function dispatch(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event
    {
        $event_name = $request_configuration->get_event() ?: $event_name;
        $metadata = $request_configuration->get_metadata();
        $event = new Resource_Controller_Event($resource);
        $this->event_dispatcher->dispatch($event, sprintf('%s.%s.%s', $metadata->get_application_name(), $metadata->get_name(), $event_name));
        return $event;
    }
    public function dispatch_multiple(string $event_name, Request_Configuration $request_configuration, $resources): Resource_Controller_Event
    {
        $event_name = $request_configuration->get_event() ?: $event_name;
        $metadata = $request_configuration->get_metadata();
        $event = new Resource_Controller_Event($resources);
        $this->event_dispatcher->dispatch($event, sprintf('%s.%s.%s', $metadata->get_application_name(), $metadata->get_name(), $event_name));
        return $event;
    }
    public function dispatch_pre_event(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event
    {
        $event_name = $request_configuration->get_event() ?: $event_name;
        $metadata = $request_configuration->get_metadata();
        $event = new Resource_Controller_Event($resource);
        $this->event_dispatcher->dispatch($event, sprintf('%s.%s.pre_%s', $metadata->get_application_name(), $metadata->get_name(), $event_name));
        return $event;
    }
    public function dispatch_post_event(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event
    {
        $event_name = $request_configuration->get_event() ?: $event_name;
        $metadata = $request_configuration->get_metadata();
        $event = new Resource_Controller_Event($resource);
        $this->event_dispatcher->dispatch($event, sprintf('%s.%s.post_%s', $metadata->get_application_name(), $metadata->get_name(), $event_name));
        return $event;
    }
    public function dispatch_initialize_event(string $event_name, Request_Configuration $request_configuration, Resource_Interface $resource): Resource_Controller_Event
    {
        $event_name = $request_configuration->get_event() ?: $event_name;
        $metadata = $request_configuration->get_metadata();
        $event = new Resource_Controller_Event($resource);
        $this->event_dispatcher->dispatch($event, sprintf('%s.%s.initialize_%s', $metadata->get_application_name(), $metadata->get_name(), $event_name));
        return $event;
    }
}