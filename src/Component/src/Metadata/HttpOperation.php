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
class Http_Operation extends Operation
{
    /** @var string|callable|null */
    protected $twig_context_factory;
    /**
     * @param array<string, string>|null $routeRequirements
     */
    public function __construct(protected ?array $methods = null, protected ?string $path = null, protected ?string $route_name = null, protected ?string $route_prefix = null, protected ?array $route_requirements = null, protected ?string $route_condition = null, protected ?int $route_priority = null, ?string $template = null, ?string $short_name = null, ?string $name = null, string|callable|null $provider = null, string|callable|null $processor = null, string|callable|null $responder = null, string|callable|null $repository = null, ?string $repository_method = null, ?array $repository_arguments = null, ?bool $read = null, ?bool $write = null, ?bool $validate = null, ?bool $deserialize = null, ?bool $serialize = null, ?string $form_type = null, ?array $form_options = null, ?array $normalization_context = null, ?array $denormalization_context = null, ?array $validation_context = null, ?string $event_short_name = null, ?string $notification_message = null, string|\Stringable|null $security = null, ?string $security_message = null, string|callable|null $twig_context_factory = null, protected ?string $redirect_to = null, protected ?string $redirect_to_route = null, protected ?array $redirect_arguments = null, protected ?array $vars = null)
    {
        parent::__construct(template: $template, shortName: $short_name, name: $name, provider: $provider, processor: $processor, responder: $responder, repository: $repository, repositoryMethod: $repository_method, repositoryArguments: $repository_arguments, read: $read, write: $write, validate: $validate, deserialize: $deserialize, serialize: $serialize, formType: $form_type, formOptions: $form_options, normalizationContext: $normalization_context, denormalizationContext: $denormalization_context, validationContext: $validation_context, eventShortName: $event_short_name, notificationMessage: $notification_message, security: $security, securityMessage: $security_message);
        $this->twig_context_factory = $twig_context_factory;
    }
    public function get_methods(): ?array
    {
        return $this->methods;
    }
    public function with_methods(array $methods): self
    {
        $self = clone $this;
        $self->methods = $methods;
        return $self;
    }
    public function get_path(): ?string
    {
        return $this->path;
    }
    public function with_path(string $path): self
    {
        $self = clone $this;
        $self->path = $path;
        return $self;
    }
    public function get_route_name(): ?string
    {
        return $this->route_name;
    }
    public function with_route_name(string $route_name): self
    {
        $self = clone $this;
        $self->route_name = $route_name;
        return $self;
    }
    public function get_route_prefix(): ?string
    {
        return $this->route_prefix;
    }
    public function with_route_prefix(?string $route_prefix): self
    {
        $self = clone $this;
        $self->route_prefix = $route_prefix;
        return $self;
    }
    /**
     * @return array<string, string>|null
     */
    public function get_route_requirements(): ?array
    {
        return $this->route_requirements;
    }
    /**
     * @param array<string, string>|null $routeRequirements
     */
    public function with_route_requirements(?array $route_requirements): self
    {
        $self = clone $this;
        $self->route_requirements = $route_requirements;
        return $self;
    }
    public function get_route_condition(): ?string
    {
        return $this->route_condition;
    }
    public function with_route_condition(?string $route_condition): self
    {
        $self = clone $this;
        $self->route_condition = $route_condition;
        return $self;
    }
    public function get_route_priority(): ?int
    {
        return $this->route_priority;
    }
    public function with_route_priority(?int $route_priority): self
    {
        $self = clone $this;
        $self->route_priority = $route_priority;
        return $self;
    }
    public function get_twig_context_factory(): callable|string|null
    {
        return $this->twig_context_factory;
    }
    public function with_twig_context_factory(callable|string|null $twig_context_factory): self
    {
        $self = clone $this;
        $self->twig_context_factory = $twig_context_factory;
        return $self;
    }
    public function get_redirect_to(): ?string
    {
        return $this->redirect_to;
    }
    public function with_redirect_to(?string $redirect_to): self
    {
        $self = clone $this;
        $self->redirect_to = $redirect_to;
        return $self;
    }
    public function get_redirect_to_route(): ?string
    {
        return $this->redirect_to_route;
    }
    public function with_redirect_to_route(string $redirect_to_route): self
    {
        $self = clone $this;
        $self->redirect_to_route = $redirect_to_route;
        return $self;
    }
    public function get_redirect_arguments(): ?array
    {
        return $this->redirect_arguments;
    }
    public function with_redirect_arguments(array $redirect_arguments): self
    {
        $self = clone $this;
        $self->redirect_arguments = $redirect_arguments;
        return $self;
    }
    public function get_vars(): ?array
    {
        return $this->vars;
    }
    public function with_vars(array $vars): self
    {
        $self = clone $this;
        $self->vars = $vars;
        return $self;
    }
}