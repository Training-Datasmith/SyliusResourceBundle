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
namespace Sylius\Resource\Symfony\Event_Dispatcher\State;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Processor_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Dispatcher_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Handler_Interface;
use Symfony\Component\Http_Foundation\Response;
/**
 * @experimental
 */
final readonly class Dispatch_Post_Write_Event_Processor implements Processor_Interface
{
    public function __construct(private Processor_Interface $processor, private Operation_Event_Dispatcher_Interface $operation_event_dispatcher, private Operation_Event_Handler_Interface $event_handler)
    {
    }
    /**
     * @inheritDoc
     */
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        $data = $this->processor->process($data, $operation, $context);
        if ($data instanceof Response) {
            return $data;
        }
        $operation_event = $this->operation_event_dispatcher->dispatch_post_event($data, $operation, $context);
        $event_response = $this->event_handler->handle_post_process_event($operation_event, $context);
        if (null !== $event_response) {
            return $event_response;
        }
        return $data;
    }
}