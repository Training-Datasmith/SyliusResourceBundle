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
use Symfony\Component\Http_Foundation\Response;
/**
 * @experimental
 */
interface Operation_Event_Handler_Interface
{
    public function handle_pre_process_event(Operation_Event $event, Context $context, ?string $new_operation = null): ?Response;
    public function handle_post_process_event(Operation_Event $event, Context $context): ?Response;
}