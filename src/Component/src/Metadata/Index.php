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
namespace Sylius\Resource\Metadata;

/**
 * @experimental
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Index extends Http_Operation implements Collection_Operation_Interface, Grid_Aware_Operation_Interface
{
    public function __construct(?array $methods = null, ?string $path = null, ?string $route_name = null, ?string $route_prefix = null, ?array $route_requirements = null, ?string $route_condition = null, ?int $route_priority = null, ?string $template = null, ?string $short_name = null, ?string $name = null, string|callable|null $provider = null, string|callable|null $processor = null, string|callable|null $responder = null, string|callable|null $repository = null, ?string $repository_method = null, ?array $repository_arguments = null, ?bool $read = null, ?bool $write = null, ?bool $validate = null, ?bool $deserialize = null, ?bool $serialize = null, ?string $form_type = null, ?array $form_options = null, ?string $event_short_name = null, ?string $notification_message = null, string|\Stringable|null $security = null, ?string $security_message = null, ?array $validation_context = null, string|callable|null $twig_context_factory = null, ?string $redirect_to_route = null, ?array $vars = null, private ?string $grid = null)
    {
        parent::__construct(methods: $methods ?? ['GET'], path: $path, routeName: $route_name, routePrefix: $route_prefix, routeRequirements: $route_requirements, routeCondition: $route_condition, routePriority: $route_priority, template: $template, shortName: $short_name ?? 'index', name: $name, provider: $provider, processor: $processor, responder: $responder, repository: $repository, repositoryMethod: $repository_method, repositoryArguments: $repository_arguments, read: $read, write: $write, validate: $validate, deserialize: $deserialize, serialize: $serialize, formType: $form_type, formOptions: $form_options, validationContext: $validation_context, eventShortName: $event_short_name, notificationMessage: $notification_message, security: $security, securityMessage: $security_message, twigContextFactory: $twig_context_factory, redirectToRoute: $redirect_to_route, vars: $vars);
    }
    public function get_grid(): ?string
    {
        return $this->grid;
    }
    public function with_grid(string $grid): self
    {
        $self = clone $this;
        $self->grid = $grid;
        return $self;
    }
}