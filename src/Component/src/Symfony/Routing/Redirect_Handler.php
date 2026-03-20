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
namespace Sylius\Resource\Symfony\Routing;

use Sylius\Bundle\Grid_Bundle\Storage\Filter_Storage_Interface;
use Sylius\Resource\Exception\InvalidArgumentException;
use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Delete_Operation_Interface;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Symfony\Expression_Language\Argument_Parser_Interface;
use Sylius\Resource\Symfony\Routing\Factory\Route_Name\Operation_Route_Name_Factory_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Property_Access\Property_Access;
use Symfony\Component\Routing\Router_Interface;
/**
 * @experimental
 */
final readonly class Redirect_Handler implements Redirect_Handler_Interface
{
    public function __construct(private Router_Interface $router, private Argument_Parser_Interface $argument_parser, private Operation_Route_Name_Factory_Interface $operation_route_name_factory, private ?Filter_Storage_Interface $filter_storage = null)
    {
    }
    public function redirect_to_resource(mixed $data, Http_Operation $operation, Request $request): Redirect_Response
    {
        if (self::REFERER === $operation->get_redirect_to()) {
            /** @var string|null $referer */
            $referer = $request->headers->get('referer');
            if (null !== $referer && $this->is_valid_referer($referer, $request)) {
                return new Redirect_Response($referer);
            }
        }
        $route = $operation->get_redirect_to_route();
        if (null === $route) {
            throw new \RuntimeException(sprintf('Operation "%s" has no redirection route, but it should.', $operation->get_name() ?? ''));
        }
        $parameters = $this->get_route_arguments($data, $operation);
        return $this->redirect_to_route($data, $route, $parameters);
    }
    public function redirect_to_operation(mixed $data, Http_Operation $operation, Request $request, string $new_operation): Redirect_Response
    {
        $route = $this->operation_route_name_factory->create_route_name($operation, $new_operation);
        $parameters = $this->get_route_arguments($data, $operation);
        return $this->redirect_to_route($data, $route, $parameters);
    }
    public function redirect_to_route(mixed $data, string $route, array $parameters = []): Redirect_Response
    {
        if (\str_ends_with($route, '_index') && [] === $parameters) {
            $parameters = $this->filter_storage?->all() ?? [];
        }
        return new Redirect_Response($this->router->generate($route, $parameters));
    }
    private function get_route_arguments(mixed $data, Http_Operation $operation): array
    {
        $resource = $operation->get_resource();
        if (null === $resource) {
            throw new \RuntimeException(sprintf('Operation "%s" has no resource, but it should.', $operation->get_name() ?? ''));
        }
        $redirect_arguments = $operation->get_redirect_arguments() ?? [];
        if ([] === $redirect_arguments && !$operation instanceof Delete_Operation_Interface && !$operation instanceof Bulk_Operation_Interface) {
            $identifier = $resource->get_identifier() ?? 'id';
            $redirect_arguments[$identifier] = 'resource.' . $identifier;
        }
        return $this->parse_resource_values($resource, $redirect_arguments, $data);
    }
    private function parse_resource_values(Resource_Metadata $resource, array $parameters, mixed $data): array
    {
        $accessor = Property_Access::create_property_accessor();
        foreach ($parameters as $key => $value) {
            if (\is_array($value)) {
                $parameters[$key] = $this->parse_resource_values($resource, $value, $data);
                continue;
            }
            if (!\is_scalar($value)) {
                throw new InvalidArgumentException(sprintf('Parameter "%s" should be a scalar or an array.', $key));
            }
            if (\is_string($value) && str_starts_with($value, 'resource.')) {
                $property_path = substr($value, 9);
                if (\is_object($data) && $accessor->is_readable($data, $property_path)) {
                    $parameters[$key] = $accessor->get_value($data, $property_path);
                    continue;
                }
            }
            $variables = ['resource' => $data];
            $resource_name = $resource->get_name();
            if (null !== $resource_name) {
                $variables[$resource_name] = $data;
            }
            $parameters[$key] = \is_string($value) ? $this->parse_string_value($value, $variables) : $value;
        }
        return $parameters;
    }
    /**
     * @param array<string, mixed> $variables
     */
    private function parse_string_value(string $value, array $variables): mixed
    {
        if (!str_starts_with($value, '@=')) {
            $value = '@=' . $value;
            trigger_deprecation('sylius/resource-bundle', '1.14', 'You passed "%s" as a string value in your redirect arguments. If this is a value that needs to be parsed using the expression language, please prefix your string with "@=". In your case, use "@=%s"."', $value, $value);
        }
        // Not reachable as long as the BC layer above is there
        if (!str_starts_with($value, '@=')) {
            return $value;
        }
        $value = substr($value, 2);
        return $this->argument_parser->parse_expression($value, $variables);
    }
    private function is_valid_referer(string $referer, Request $request): bool
    {
        $parsed = parse_url($referer);
        if ($parsed === false) {
            return false;
        }
        // Relative URL → OK
        if (!isset($parsed['host'])) {
            return true;
        }
        // Same host only
        return $parsed['host'] === $request->get_host();
    }
}