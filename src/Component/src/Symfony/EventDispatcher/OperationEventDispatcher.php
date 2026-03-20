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
namespace Sylius\Resource\Symfony\Event_Dispatcher;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
/**
 * @experimental
 */
final readonly class Operation_Event_Dispatcher implements Operation_Event_Dispatcher_Interface
{
    public function __construct(private Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function dispatch(mixed $data, Operation $operation, Context $context): Operation_Event
    {
        return $this->dispatch_event($data, $operation, $context);
    }
    public function dispatch_bulk_event(mixed $data, Operation $operation, Context $context): Operation_Event
    {
        return $this->dispatch_event($data, $operation, $context, 'bulk');
    }
    public function dispatch_pre_event(mixed $data, Operation $operation, Context $context): Operation_Event
    {
        return $this->dispatch_event($data, $operation, $context, 'pre');
    }
    public function dispatch_post_event(mixed $data, Operation $operation, Context $context): Operation_Event
    {
        return $this->dispatch_event($data, $operation, $context, 'post');
    }
    public function dispatch_initialize_event(mixed $data, Operation $operation, Context $context): Operation_Event
    {
        return $this->dispatch_event($data, $operation, $context, 'initialize');
    }
    private function dispatch_event(mixed $data, Operation $operation, Context $context, ?string $event_type = null): Operation_Event
    {
        $operation_event = new Operation_Event($data, ['operation' => $operation, 'context' => $context]);
        $resource = $operation->get_resource();
        if (null === $resource) {
            return $operation_event;
        }
        $event_name = sprintf('%s.%s.%s%s', $resource->get_application_name() ?? '', $resource->get_name() ?? '', $event_type ? $event_type . '_' : '', $operation->get_event_short_name() ?? '');
        $this->event_dispatcher->dispatch($operation_event, $event_name);
        return $operation_event;
    }
}