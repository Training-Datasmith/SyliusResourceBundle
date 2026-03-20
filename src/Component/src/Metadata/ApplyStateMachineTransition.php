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
final class Apply_State_Machine_Transition extends Http_Operation implements Update_Operation_Interface, State_Machine_Aware_Operation_Interface
{
    public function __construct(?array $methods = null, ?string $path = null, ?string $route_name = null, ?string $route_prefix = null, ?string $route_condition = null, ?int $route_priority = null, ?string $template = null, ?string $short_name = null, ?string $name = null, string|callable|null $provider = null, string|callable|null $processor = null, string|callable|null $responder = null, string|callable|null $repository = null, ?string $repository_method = null, ?array $repository_arguments = null, ?bool $read = null, ?bool $write = null, ?bool $validate = null, ?string $form_type = null, ?array $form_options = null, ?string $event_short_name = null, ?string $notification_message = null, string|\Stringable|null $security = null, ?string $security_message = null, ?string $redirect_to_route = null, ?array $redirect_arguments = null, private ?string $state_machine_component = null, private ?string $state_machine_transition = null, private ?string $state_machine_graph = null)
    {
        parent::__construct(methods: $methods ?? ['PUT', 'PATCH', 'POST'], path: $path, routeName: $route_name, routePrefix: $route_prefix, routeCondition: $route_condition, routePriority: $route_priority, template: $template, shortName: $short_name ?? $state_machine_transition ?? 'apply_state_machine_transition', name: $name, provider: $provider, processor: $processor, responder: $responder, repository: $repository, repositoryMethod: $repository_method, repositoryArguments: $repository_arguments, read: $read, write: $write, validate: $validate ?? false, formType: $form_type, formOptions: $form_options, eventShortName: $event_short_name, notificationMessage: $notification_message, security: $security, securityMessage: $security_message, redirectToRoute: $redirect_to_route, redirectArguments: $redirect_arguments);
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
}