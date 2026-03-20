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
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Exception\Write_Resource_Exception;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Processor_Interface;
use Symfony\Component\Http_Foundation\Response;
/**
 * @experimental
 */
final readonly class Write_Processor implements Processor_Interface
{
    public function __construct(private Processor_Interface $processor, private Processor_Interface $locator_processor)
    {
    }
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        if ($data instanceof Response || !($operation->can_write() ?? true) || !$operation->get_processor()) {
            return $this->processor->process($data, $operation, $context);
        }
        try {
            return $this->processor->process($this->locator_processor->process($data, $operation, $context), $operation, $context);
        } catch (Write_Resource_Exception $exception) {
            $request = $context->get(Request_Option::class)?->request();
            $request?->attributes->set('error', $exception->get_message());
            return $this->processor->process(null, $operation, $context);
        }
    }
}