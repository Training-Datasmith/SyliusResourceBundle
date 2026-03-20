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
namespace Sylius\Resource\Symfony\Serializer\State;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Delete_Operation_Interface;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Provider_Interface;
use Symfony\Component\Serializer\Normalizer\Abstract_Normalizer;
use Symfony\Component\Serializer\Serializer_Interface;
/**
 * @experimental
 */
final readonly class Deserialize_Provider implements Provider_Interface
{
    public function __construct(private Provider_Interface $decorated, private ?Serializer_Interface $serializer)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $data = $this->decorated->provide($operation, $context);
        if (!$operation instanceof Http_Operation) {
            return $data;
        }
        $request = $context->get(Request_Option::class)?->request() ?? null;
        if (!$request) {
            return $data;
        }
        if (!($operation->can_deserialize() ?? true)) {
            return $data;
        }
        $resource_class = $operation->get_resource()?->get_class();
        /** @var string $format */
        $format = $request->get_request_format();
        if (null === $resource_class || 'html' === $format || $request->is_method_safe() || $operation instanceof Delete_Operation_Interface) {
            return $data;
        }
        if (null === $this->serializer) {
            throw new \LogicException(sprintf('You can not use the "%s" format if the Serializer is not available. Try running "composer require symfony/serializer".', $format));
        }
        $denormalization_context = $operation->get_denormalization_context() ?? [];
        $method = $request->get_method();
        if (null !== $data && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            $denormalization_context[Abstract_Normalizer::OBJECT_TO_POPULATE] = $data;
        }
        return $this->serializer->deserialize($request->get_content(), $resource_class, $format, $denormalization_context);
    }
}