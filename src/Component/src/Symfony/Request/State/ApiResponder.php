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
namespace Sylius\Resource\Symfony\Request\State;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Delete_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Update_Operation_Interface;
use Sylius\Resource\State\Responder_Interface;
use Sylius\Resource\Symfony\Response\Headers_Initiator_Interface;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Api_Responder implements Responder_Interface
{
    public function __construct(private Headers_Initiator_Interface $headers_initializer)
    {
    }
    public function respond(mixed $data, Operation $operation, Context $context): ?Response
    {
        $request = $context->get(Request_Option::class)?->request();
        if (null === $request) {
            return null;
        }
        $is_valid = $request->attributes->get_boolean('is_valid', true);
        Assert::string($data, 'Data are not serialized but it should.');
        /** @var string $format */
        $format = $request->get_request_format();
        /** @var string $mimeType */
        $mime_type = $request->get_mime_type($format);
        $headers = $this->headers_initializer->initialize_headers($mime_type);
        $status = Response::HTTP_OK;
        if ($operation instanceof Create_Operation_Interface) {
            $status = Response::HTTP_CREATED;
        }
        if ($operation instanceof Delete_Operation_Interface || $operation instanceof Update_Operation_Interface) {
            $status = Response::HTTP_NO_CONTENT;
        }
        return new Response($data, $is_valid ? $status : Response::HTTP_UNPROCESSABLE_ENTITY, $headers);
    }
}