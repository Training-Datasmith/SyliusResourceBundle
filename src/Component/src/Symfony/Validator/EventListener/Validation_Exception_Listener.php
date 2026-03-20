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
namespace Sylius\Resource\Symfony\Validator\Event_Listener;

use Sylius\Resource\Symfony\Validator\Exception\Constraint_Violation_List_Aware_Exception_Interface;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Event\Exception_Event;
use Symfony\Component\Serializer\Serializer_Interface;
/**
 * Handles validation errors.
 *
 * @experimental
 *
 * @final
 */
class Validation_Exception_Listener
{
    public function __construct(private readonly ?Serializer_Interface $serializer = null)
    {
    }
    /**
     * Returns a list of violations normalized in the Hydra format.
     */
    public function on_kernel_exception(Exception_Event $event): void
    {
        $exception = $event->get_throwable();
        if (!$exception instanceof Constraint_Violation_List_Aware_Exception_Interface) {
            return;
        }
        if (null === $this->serializer) {
            throw new \LogicException('The Symfony Serializer is not available. Try running "composer require symfony/serializer".');
        }
        $request = $event->get_request();
        /** @var string $format */
        $format = $request->get_request_format();
        /** @var string $mimeType */
        $mime_type = $request->get_mime_type($format);
        $event->set_response(new Response($this->serializer->serialize($exception->get_constraint_violation_list(), $format), Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => sprintf('%s; charset=utf-8', $mime_type), 'X-Content-Type-Options' => 'nosniff', 'X-Frame-Options' => 'deny']));
    }
}