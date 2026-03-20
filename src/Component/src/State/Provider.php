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
final readonly class Provider implements Provider_Interface
{
    public function __construct(private Container_Interface $locator)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $provider = $operation->get_provider();
        if (null === $provider) {
            return null;
        }
        if (\is_callable($provider)) {
            return $provider($operation, $context);
        }
        if (!$this->locator->has($provider)) {
            throw new \RuntimeException(sprintf('Provider "%s" not found on operation "%s"', $provider, $operation->get_name() ?? ''));
        }
        $provider_instance = $this->locator->get($provider);
        Assert::is_instance_of($provider_instance, Provider_Interface::class);
        return $provider_instance->provide($operation, $context);
    }
}