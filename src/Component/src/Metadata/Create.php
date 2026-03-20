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
final class Create extends Http_Operation implements Create_Operation_Interface, State_Machine_Aware_Operation_Interface, Factory_Aware_Operation_Interface
{
    /** @var string|callable|false|null */
    private $factory;
    public function __construct(?array $methods = null, ?string $path = null, ?string $route_name = null, ?string $route_prefix = null, ?array $route_requirements = null, ?string $route_condition = null, ?int $route_priority = null, ?string $template = null, ?string $short_name = null, ?string $name = null, string|callable|null $provider = null, string|callable|null $processor = null, string|callable|null $responder = null, string|callable|null $repository = null, ?array $repository_arguments = null, ?string $repository_method = null, string|callable|false|null $factory = null, private ?string $factory_method = null, private ?array $factory_arguments = [], ?bool $read = null, ?bool $write = null, ?bool $validate = null, ?bool $deserialize = null, ?bool $serialize = null, ?string $form_type = null, ?array $form_options = null, ?array $validation_context = null, ?string $event_short_name = null, ?string $notification_message = null, string|\Stringable|null $security = null, ?string $security_message = null, string|callable|null $twig_context_factory = null, ?string $redirect_to = null, ?string $redirect_to_route = null, ?array $redirect_arguments = null, ?array $vars = null, private ?string $state_machine_component = null, private ?string $state_machine_transition = null, private ?string $state_machine_graph = null)
    {
        parent::__construct(methods: $methods ?? ['GET', 'POST'], path: $path, routeName: $route_name, routePrefix: $route_prefix, routeRequirements: $route_requirements, routeCondition: $route_condition, routePriority: $route_priority, template: $template, shortName: $short_name ?? 'create', name: $name, provider: $provider, processor: $processor, responder: $responder, repository: $repository, repositoryMethod: $repository_method, repositoryArguments: $repository_arguments, read: $read, write: $write, validate: $validate, deserialize: $deserialize, serialize: $serialize, formType: $form_type, formOptions: $form_options, validationContext: $validation_context, eventShortName: $event_short_name, notificationMessage: $notification_message, security: $security, securityMessage: $security_message, twigContextFactory: $twig_context_factory, redirectTo: $redirect_to, redirectToRoute: $redirect_to_route, redirectArguments: $redirect_arguments, vars: $vars);
        $this->factory = $factory;
    }
    public function get_state_machine_component(): ?string
    {
        return $this->state_machine_component;
    }
    public function with_state_machine_component(?string $state_machine_component): self
    {
        $self = clone $this;
        $self->state_machine_component = $state_machine_component;
        return $self;
    }
    public function get_state_machine_transition(): ?string
    {
        return $this->state_machine_transition;
    }
    public function with_state_machine_transition(string $state_machine_transition): self
    {
        $self = clone $this;
        $self->state_machine_transition = $state_machine_transition;
        return $self;
    }
    public function get_state_machine_graph(): ?string
    {
        return $this->state_machine_graph;
    }
    public function with_state_machine_graph(string $state_machine_graph): self
    {
        $self = clone $this;
        $self->state_machine_graph = $state_machine_graph;
        return $self;
    }
    public function get_factory(): callable|string|false|null
    {
        return $this->factory;
    }
    public function with_factory(string|callable|false|null $factory): self
    {
        $self = clone $this;
        $self->factory = $factory;
        return $self;
    }
    public function get_factory_method(): ?string
    {
        return $this->factory_method;
    }
    public function with_factory_method(string $factory_method): self
    {
        $self = clone $this;
        $self->factory_method = $factory_method;
        return $self;
    }
    public function get_factory_arguments(): ?array
    {
        return $this->factory_arguments;
    }
    public function with_factory_arguments(array $factory_arguments): self
    {
        $self = clone $this;
        $self->factory_arguments = $factory_arguments;
        return $self;
    }
}