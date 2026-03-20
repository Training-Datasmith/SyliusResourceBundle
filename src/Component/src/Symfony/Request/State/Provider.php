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
namespace Sylius\Resource\Symfony\Request\State;

use Pagerfanta\Pagerfanta_Interface;
use Psr\Container\Container_Interface;
use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Create_Paginator_Trait;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Exception\InvalidArgumentException;
use Sylius\Resource\Exception\RuntimeException;
use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Collection_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Reflection\Callable_Reflection;
use Sylius\Resource\State\Provider_Interface;
use Sylius\Resource\Symfony\Expression_Language\Argument_Parser_Interface;
use Sylius\Resource\Symfony\Request\Repository_Argument_Resolver;
/**
 * @experimental
 */
final readonly class Provider implements Provider_Interface
{
    public function __construct(private Container_Interface $locator, private Repository_Argument_Resolver $argument_resolver, private Argument_Parser_Interface $argument_parser)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $request = $context->get(Request_Option::class)?->request();
        $repository = $operation->get_repository();
        if (null === $request || null === $repository) {
            return null;
        }
        $repository_instance = null;
        $arguments = $this->parse_argument_values($operation->get_repository_arguments() ?? []);
        if (\is_string($repository)) {
            $default_method = $operation instanceof Collection_Operation_Interface ? 'createPaginator' : 'findOneBy';
            if ($operation instanceof Bulk_Operation_Interface) {
                $default_method = 'findById';
            }
            $custom_method = $operation->get_repository_method();
            $method = $custom_method ?? $default_method;
            if (!$this->locator->has($repository)) {
                throw new RuntimeException(sprintf('Repository "%s" not found on operation "%s".', $repository, $operation->get_name() ?? ''));
            }
            /** @var object $repositoryInstance */
            $repository_instance = $this->locator->get($repository);
            if (!str_starts_with($method, 'find') && !\method_exists($repository_instance, $method)) {
                $error_message = sprintf('Method "%s" not found on repository "%s". You can either add it or configure another one in the repositoryMethod option for your operation.', $method, get_debug_type($repository_instance));
                if ('createPaginator' === $method) {
                    $error_message = sprintf('Method "%s" not found on repository "%s". You can use the "%s" trait on this repository class.', $method, get_debug_type($repository_instance), Create_Paginator_Trait::class);
                }
                throw new RuntimeException($error_message);
            }
            // make it as callable
            /** @var callable $repository */
            $repository = [$repository_instance, $method];
        }
        try {
            $reflector = Callable_Reflection::from($repository);
        } catch (\Reflection_Exception $exception) {
            if (null === $repository_instance) {
                throw $exception;
            }
            /** @var callable $callable */
            $callable = [$repository_instance, '__call'];
            $reflector = Callable_Reflection::from($callable);
        }
        if ([] === $arguments) {
            $arguments = $this->argument_resolver->get_arguments($request, $reflector);
        }
        $data = $repository(...$arguments);
        if ($data instanceof Pagerfanta_Interface) {
            $current_page = $request->query->get_int('page', 1);
            $data->set_current_page($current_page);
        }
        return $data;
    }
    private function parse_argument_values(array $arguments): array
    {
        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                $arguments[$key] = $this->parse_argument_values($value);
                continue;
            }
            if (!\is_scalar($value)) {
                throw new InvalidArgumentException(sprintf('Parameter "%s" should be a scalar or an array.', $key));
            }
            $arguments[$key] = \is_string($value) ? $this->parse_string_value($value) : $value;
        }
        return $arguments;
    }
    private function parse_string_value(string $value): mixed
    {
        if (!str_starts_with($value, '@=')) {
            $value = '@=' . $value;
            trigger_deprecation('sylius/resource-bundle', '1.14', 'You passed "%s" as a string value in your repository arguments. If this is a value that needs to be parsed using the expression language, please prefix your string with "@=". In your case, use "@=%s"."', $value, $value);
        }
        // Not reachable as long as the BC layer above is there
        if (!str_starts_with($value, '@=')) {
            return $value;
        }
        $value = substr($value, 2);
        return $this->argument_parser->parse_expression($value);
    }
}