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
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Resource_Actions;
use Sylius\Resource\State\Processor_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Dispatcher_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Handler_Interface;
/**
 * @experimental
 */
final readonly class Dispatch_Pre_Write_Event_Processor implements Processor_Interface
{
    public function __construct(private Processor_Interface $processor, private Operation_Event_Dispatcher_Interface $operation_event_dispatcher, private Operation_Event_Handler_Interface $event_handler)
    {
    }
    /**
     * @inheritDoc
     */
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        $operation_event = $this->operation_event_dispatcher->dispatch_pre_event($data, $operation, $context);
        $event_response = $this->event_handler->handle_pre_process_event($operation_event, $context, $operation instanceof Create_Operation_Interface ? Resource_Actions::INDEX : null);
        if (null !== $event_response) {
            return $event_response;
        }
        return $this->processor->process($data, $operation, $context);
    }
}