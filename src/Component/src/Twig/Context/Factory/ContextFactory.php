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
namespace Sylius\Resource\Twig\Context\Factory;

use Psr\Container\Container_Interface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Operation;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Context_Factory implements Context_Factory_Interface
{
    public function __construct(private Container_Interface $locator)
    {
    }
    public function create(mixed $data, Operation $operation, Context $context): array
    {
        if (!$operation instanceof Http_Operation || null === $twig_context_factory = $operation->get_twig_context_factory()) {
            return [];
        }
        if (\is_callable($twig_context_factory)) {
            return $twig_context_factory($data, $operation, $context);
        }
        if (!$this->locator->has($twig_context_factory)) {
            throw new \RuntimeException(sprintf('Twig context factory "%s" not found on operation "%s"', $twig_context_factory, $operation->get_name() ?? ''));
        }
        /** @var ContextFactoryInterface $twigContextFactoryInstance */
        $twig_context_factory_instance = $this->locator->get($twig_context_factory);
        Assert::is_instance_of($twig_context_factory_instance, Context_Factory_Interface::class);
        return $twig_context_factory_instance->create($data, $operation, $context);
    }
}