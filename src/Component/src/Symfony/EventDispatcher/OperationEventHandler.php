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
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Symfony\Routing\Redirect_Handler_Interface;
use Sylius\Resource\Symfony\Session\Flash\Flash_Helper_Interface;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Exception\Http_Exception;
/**
 * @experimental
 */
final readonly class Operation_Event_Handler implements Operation_Event_Handler_Interface
{
    public function __construct(private Redirect_Handler_Interface $redirect_handler, private Flash_Helper_Interface $flash_helper)
    {
    }
    public function handle_pre_process_event(Operation_Event $event, Context $context, ?string $new_operation = null): ?Response
    {
        if (!$event->is_stopped()) {
            return null;
        }
        $request = $context->get(Request_Option::class)?->request();
        if ('html' !== $request?->get_request_format()) {
            throw new Http_Exception($event->get_error_code(), $event->get_message());
        }
        $this->flash_helper->add_flash_from_event($event, $context);
        if (null !== $operation_event_response = $event->get_response()) {
            return $operation_event_response;
        }
        $operation = $event->get_operation();
        if ($operation instanceof Http_Operation && null !== $request) {
            if (null === $new_operation) {
                return $this->redirect_handler->redirect_to_resource($event->get_subject(), $operation, $request);
            }
            return $this->redirect_handler->redirect_to_operation($event->get_subject(), $operation, $request, $new_operation);
        }
        return null;
    }
    public function handle_post_process_event(Operation_Event $event, Context $context): ?Response
    {
        $request = $context->get(Request_Option::class)?->request();
        if ('html' !== $request?->get_request_format()) {
            return null;
        }
        return $event->get_response();
    }
}