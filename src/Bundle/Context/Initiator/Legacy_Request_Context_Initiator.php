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
namespace Sylius\Bundle\Resource_Bundle\Context\Initiator;

use Sylius\Bundle\Resource_Bundle\Context\Option\Request_Configuration_Option;
use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration_Factory_Interface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Initiator\Request_Context_Initiator_Interface;
use Sylius\Resource\Context\Option\Metadata_Option;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Symfony\Expression_Language\Vars_Resolver_Interface;
use Symfony\Component\Http_Foundation\Request;
final readonly class Legacy_Request_Context_Initiator implements Request_Context_Initiator_Interface
{
    public function __construct(private Registry_Interface $resource_registry, private Request_Configuration_Factory_Interface $request_configuration_factory, private Request_Context_Initiator_Interface $decorated, private ?Vars_Resolver_Interface $vars_resolver = null)
    {
        if (null === $vars_resolver) {
            trigger_deprecation('sylius/resource-bundle', '1.14', 'Not passing an instance of "%s" as the fourth constructor argument for "%s" is deprecated and will not be supported in 2.0.', Vars_Resolver_Interface::class, self::class);
        }
    }
    public function initialize_context(Request $request): Context
    {
        $context = $this->decorated->initialize_context($request);
        if ([] === $attributes = $request->attributes->all('_sylius')) {
            return $context;
        }
        /** @var string|class-string|null $resource */
        $resource = $attributes['resource'] ?? null;
        if (null === $resource) {
            return $context;
        }
        if (str_contains($resource, '.')) {
            $metadata = $this->resource_registry->get($resource);
        } else {
            $metadata = $this->resource_registry->get_by_class($resource);
        }
        $configuration = $this->request_configuration_factory->create($metadata, $request);
        $configuration_vars = $this->resolve_vars($configuration->get_vars());
        $configuration->get_parameters()->set('vars', $configuration_vars);
        return $context->with(new Metadata_Option($metadata), new Request_Configuration_Option($configuration));
    }
    private function resolve_vars(array $vars): array
    {
        if (null === $this->vars_resolver) {
            return $vars;
        }
        return $this->vars_resolver->resolve($vars);
    }
}