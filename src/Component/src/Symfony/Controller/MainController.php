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
namespace Sylius\Resource\Symfony\Controller;

use Sylius\Resource\Context\Initiator\Request_Context_Initiator_Interface;
use Sylius\Resource\Exception\RuntimeException;
use Sylius\Resource\Metadata\Operation\Http_Operation_Initiator_Interface;
use Sylius\Resource\State\Processor_Interface;
use Sylius\Resource\State\Provider_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
/**
 * @experimental
 */
final readonly class Main_Controller
{
    public function __construct(private Http_Operation_Initiator_Interface $operation_initiator, private Request_Context_Initiator_Interface $request_context_initiator, private Provider_Interface $provider, private Processor_Interface $processor)
    {
    }
    public function __invoke(Request $request): Response
    {
        $operation = $this->operation_initiator->initialize_operation($request);
        if (null === $operation) {
            throw new RuntimeException('Operation should not be null.');
        }
        $context = $this->request_context_initiator->initialize_context($request);
        if (null === $operation->can_write()) {
            $operation = $operation->with_write(!$request->is_method_safe());
        }
        $data = $this->provider->provide($operation, $context);
        $valid = $request->attributes->get_boolean('is_valid', true);
        if (!$valid) {
            $operation = $operation->with_write(false);
        }
        return $this->processor->process($data, $operation, $context);
    }
}