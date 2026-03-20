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
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Processor_Interface;
use Sylius\Resource\Symfony\Session\Flash\Flash_Helper_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
/**
 * @experimental
 */
final readonly class Flash_Processor implements Processor_Interface
{
    public function __construct(private Processor_Interface $processor, private Flash_Helper_Interface $flash_helper)
    {
    }
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        $request = $context->get(Request_Option::class)?->request();
        if (null === $request) {
            return $this->processor->process($data, $operation, $context);
        }
        $format = $request->get_request_format();
        if ($data instanceof Response || $request->is_method_safe() || $format !== 'html' || !($operation->can_write() ?? true)) {
            return $this->processor->process($data, $operation, $context);
        }
        $this->add_flash($request, $operation, $context);
        return $this->processor->process($data, $operation, $context);
    }
    private function add_flash(Request $request, Operation $operation, Context $context): void
    {
        if ($request->attributes->has('error')) {
            $this->flash_helper->add_error_flash($operation, $context);
            return;
        }
        $this->flash_helper->add_success_flash($operation, $context);
    }
}