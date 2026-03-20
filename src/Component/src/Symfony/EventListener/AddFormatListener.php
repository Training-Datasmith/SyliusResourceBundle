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
namespace Sylius\Resource\Symfony\Event_Listener;

use Negotiation\Base_Accept;
use Negotiation\Negotiator;
use Sylius\Resource\Metadata\Operation\Http_Operation_Initiator_Interface;
use Symfony\Component\Http_Kernel\Event\Request_Event;
use Symfony\Component\Http_Kernel\Exception\Not_Acceptable_Http_Exception;
/**
 * @experimental
 */
final readonly class Add_Format_Listener
{
    public function __construct(private Http_Operation_Initiator_Interface $operation_initiator, private Negotiator $negotiator)
    {
    }
    public function on_kernel_request(Request_Event $event): void
    {
        $request = $event->get_request();
        $operation = $this->operation_initiator->initialize_operation($request);
        if (null === $operation) {
            return;
        }
        $mime_types = ['text/html', 'application/json', 'application/xml'];
        // First, try to guess the format from the Accept header
        $accept = $request->headers->get('Accept');
        if (null !== $accept) {
            /** @var BaseAccept|null $mediaType */
            $media_type = $this->negotiator->get_best($accept, $mime_types);
            if (null !== $media_type) {
                $request->set_request_format($request->get_format($media_type->get_type()));
                return;
            }
        }
        // Then use the Symfony request format if available and applicable
        $request_format = $request->get_request_format(null);
        if (null !== $request_format) {
            $mime_type = $request->get_mime_type($request_format);
            if (\in_array($mime_type, $mime_types, true)) {
                return;
            }
            throw $this->get_not_acceptable_http_exception($mime_type ?? '', $mime_types);
        }
    }
    /**
     * Retrieves an instance of NotAcceptableHttpException.
     */
    private function get_not_acceptable_http_exception(string $accept, array $mime_types): Not_Acceptable_Http_Exception
    {
        return new Not_Acceptable_Http_Exception(sprintf('Requested format "%s" is not supported. Supported MIME types are "%s".', $accept, implode('", "', $mime_types)));
    }
}