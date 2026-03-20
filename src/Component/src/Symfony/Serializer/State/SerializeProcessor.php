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
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Processor_Interface;
use Symfony\Component\Serializer\Serializer_Interface;
/**
 * Serializes the data to the requested format.
 *
 * @experimental
 */
final readonly class Serialize_Processor implements Processor_Interface
{
    public function __construct(private Processor_Interface $processor, private ?Serializer_Interface $serializer)
    {
    }
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        $request = $context->get(Request_Option::class)?->request();
        if (null === $request) {
            return $this->processor->process($data, $operation, $context);
        }
        /** @var string $format */
        $format = $request->get_request_format();
        if ('html' === $format || !($operation->can_serialize() ?? true)) {
            return $this->processor->process($data, $operation, $context);
        }
        if (null === $this->serializer) {
            throw new \LogicException(sprintf('You can not use the "%s" format if the Serializer is not available. Try running "composer require symfony/serializer".', $format));
        }
        $serialized = $this->serializer->serialize($data, $format, $operation->get_normalization_context() ?? []);
        return $this->processor->process($serialized, $operation, $context);
    }
}