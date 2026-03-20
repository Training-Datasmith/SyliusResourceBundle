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
use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Processor_Interface;
/**
 * @experimental
 */
final readonly class Bulk_Aware_Processor implements Processor_Interface
{
    public function __construct(private Processor_Interface $processor)
    {
    }
    /**
     * @inheritDoc
     */
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        if (!$operation instanceof Bulk_Operation_Interface || !\is_iterable($data)) {
            return $this->processor->process($data, $operation, $context);
        }
        foreach ($data as $item) {
            $this->processor->process($item, $operation, $context);
        }
        return null;
    }
}