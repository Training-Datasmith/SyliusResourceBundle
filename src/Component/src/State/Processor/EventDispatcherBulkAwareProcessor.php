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
namespace Sylius\Resource\State\Processor;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Processor_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Operation_Event_Dispatcher_Interface;
/**
 * @experimental
 */
final readonly class Event_Dispatcher_Bulk_Aware_Processor implements Processor_Interface
{
    public function __construct(private Processor_Interface $decorated, private Operation_Event_Dispatcher_Interface $operation_event_dispatcher)
    {
    }
    /**
     * @inheritDoc
     */
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        if ($operation instanceof Bulk_Operation_Interface && \is_iterable($data)) {
            $this->operation_event_dispatcher->dispatch_bulk_event($data, $operation, $context);
        }
        return $this->decorated->process($data, $operation, $context);
    }
}