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
use Sylius\Resource\Factory\Factory_Interface as ResourceFactoryInterface;
use Sylius\Resource\Metadata\Factory_Aware_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Symfony\Expression_Language\Argument_Parser_Interface;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Factory implements Factory_Interface
{
    public function __construct(private Container_Interface $locator, private Argument_Parser_Interface $argument_parser)
    {
    }
    public function create(Operation $operation, Context $context): ?object
    {
        if (!$operation instanceof Factory_Aware_Operation_Interface) {
            return null;
        }
        $factory = $operation->get_factory();
        if (!$factory) {
            return null;
        }
        $arguments = $this->parse_argument_values($operation->get_factory_arguments() ?? []);
        if (\is_callable($factory)) {
            return $factory(...$arguments);
        }
        if (!$this->locator->has($factory)) {
            throw new \RuntimeException(sprintf('Factory "%s" not found on operation "%s"', $factory, $operation->get_name() ?? ''));
        }
        $factory_instance = $this->locator->get($factory);
        Assert::is_instance_of($factory_instance, Resource_Factory_Interface::class);
        $factory_method = $operation->get_factory_method();
        if (null === $factory_method) {
            throw new \RuntimeException(sprintf('No Factory method was configured on operation "%s"', $operation->get_name() ?? ''));
        }
        return $factory_instance->{$factory_method}(...$arguments);
    }
    private function parse_argument_values(array $arguments): array
    {
        foreach ($arguments as $key => $value) {
            if (!str_starts_with((string) $value, '@=')) {
                $value = '@=' . $value;
                trigger_deprecation('sylius/resource-bundle', '1.14', 'You passed "%s" as a string value in your repository arguments. If this is a value that needs to be parsed using the expression language, please prefix your string with "@=". In your case, use "@=%s"."', $value, $value);
            }
            // Not reachable as long as the BC layer above is there
            if (!str_starts_with((string) $value, '@=')) {
                $arguments[$key] = $value;
                continue;
            }
            $value = substr((string) $value, 2);
            $arguments[$key] = $this->argument_parser->parse_expression($value);
        }
        return $arguments;
    }
}