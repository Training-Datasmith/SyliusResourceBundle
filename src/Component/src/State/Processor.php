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
namespace Sylius\Resource\State;

use Psr\Container\Container_Interface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Processor implements Processor_Interface
{
    public function __construct(private Container_Interface $locator)
    {
    }
    /**
     * @inheritDoc
     */
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        $processor = $operation->get_processor();
        if (null === $processor) {
            return null;
        }
        if (\is_callable($processor)) {
            return $processor($data, $operation, $context);
        }
        if (!$this->locator->has($processor)) {
            throw new \RuntimeException(sprintf('Processor "%s" not found on operation "%s"', $processor, $operation->get_name() ?? ''));
        }
        /** @var ProcessorInterface $processorInstance */
        $processor_instance = $this->locator->get($processor);
        Assert::is_instance_of($processor_instance, Processor_Interface::class);
        return $processor_instance->process($data, $operation, $context);
    }
}