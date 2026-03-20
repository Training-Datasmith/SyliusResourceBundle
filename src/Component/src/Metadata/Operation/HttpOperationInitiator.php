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
namespace Sylius\Resource\Metadata\Operation;

use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Factory\Resource_Metadata_Collection_Factory_Interface;
use Sylius\Resource\Symfony\Expression_Language\Vars_Resolver_Interface;
use Symfony\Component\Http_Foundation\Request;
final readonly class Http_Operation_Initiator implements Http_Operation_Initiator_Interface
{
    public function __construct(private Registry_Interface $resource_registry, private Resource_Metadata_Collection_Factory_Interface $resource_metadata_collection_factory, private ?Vars_Resolver_Interface $vars_resolver = null)
    {
        if (null === $vars_resolver) {
            trigger_deprecation('sylius/resource-bundle', '1.14', 'Not passing an instance of "%s" as the third constructor argument for "%s" is deprecated and will not be supported in 2.0.', Vars_Resolver_Interface::class, self::class);
        }
    }
    public function initialize_operation(Request $request): ?Http_Operation
    {
        /** @var string|null $operationName */
        $operation_name = $request->attributes->get('_route');
        $sylius_options = $attributes = $request->attributes->all('_sylius');
        /** @var string|class-string|null $resource */
        $resource = $attributes['resource'] ?? null;
        if ([] === $sylius_options || null === $resource || null === $operation_name) {
            return null;
        }
        if (str_contains($resource, '.')) {
            $metadata = $this->resource_registry->get($resource);
        } else {
            $metadata = $this->resource_registry->get_by_class($resource);
        }
        $sylius_options['resource_class'] = $metadata->get_class('model');
        $request->attributes->set('_sylius', $sylius_options);
        /** @var HttpOperation $operation */
        $operation = $this->resource_metadata_collection_factory->create($metadata->get_class('model'))->get_operation($metadata->get_alias(), $operation_name);
        return $this->get_operation_with_vars($operation);
    }
    private function get_operation_with_vars(Http_Operation $operation): Http_Operation
    {
        $operation_vars = $operation->get_vars();
        $resolved_operation_vars = $operation_vars !== null ? $this->resolve_vars($operation_vars) : null;
        $resource_vars = $operation->get_resource()?->get_vars();
        $resolved_resource_vars = $resource_vars !== null ? $this->resolve_vars($resource_vars) : null;
        if (null === $resolved_operation_vars && null === $resolved_resource_vars) {
            return $operation;
        }
        $merged_vars = array_merge($resolved_resource_vars ?? [], $resolved_operation_vars ?? []);
        return $operation->with_vars($merged_vars);
    }
    private function resolve_vars(array $vars): array
    {
        if (null === $this->vars_resolver) {
            return $vars;
        }
        return $this->vars_resolver->resolve($vars);
    }
}