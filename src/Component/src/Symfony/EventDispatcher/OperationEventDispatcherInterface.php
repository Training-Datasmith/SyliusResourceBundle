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
/**
 * @experimental
 */
interface Operation_Event_Dispatcher_Interface
{
    public function dispatch(mixed $data, Operation $operation, Context $context): Operation_Event;
    public function dispatch_bulk_event(mixed $data, Operation $operation, Context $context): Operation_Event;
    public function dispatch_pre_event(mixed $data, Operation $operation, Context $context): Operation_Event;
    public function dispatch_post_event(mixed $data, Operation $operation, Context $context): Operation_Event;
    public function dispatch_initialize_event(mixed $data, Operation $operation, Context $context): Operation_Event;
}