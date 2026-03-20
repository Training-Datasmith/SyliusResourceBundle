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
abstract class Operation
{
    private ?Resource_Metadata $resource = null;
    /** @var string|callable|null */
    protected $provider;
    /** @var string|callable|null */
    protected $processor;
    /** @var string|callable|null */
    protected $responder;
    /** @var string|callable|null */
    protected $repository;
    public function __construct(protected ?string $template = null, protected ?string $short_name = null, protected ?string $name = null, string|callable|null $provider = null, string|callable|null $processor = null, string|callable|null $responder = null, string|callable|null $repository = null, protected ?string $repository_method = null, protected ?array $repository_arguments = null, protected ?bool $read = null, protected ?bool $write = null, protected ?bool $validate = null, protected ?bool $deserialize = null, protected ?bool $serialize = null, protected ?string $form_type = null, protected ?array $form_options = null, protected ?array $normalization_context = null, protected ?array $denormalization_context = null, protected ?array $validation_context = null, protected ?string $event_short_name = null, protected ?string $notification_message = null, protected string|\Stringable|null $security = null, protected ?string $security_message = null)
    {
        $this->provider = $provider;
        $this->processor = $processor;
        $this->responder = $responder;
        $this->repository = $repository;
    }
    public function get_resource(): ?Resource_Metadata
    {
        return $this->resource;
    }
    public function with_resource(Resource_Metadata $resource): self
    {
        $self = clone $this;
        $self->resource = $resource;
        return $self;
    }
    public function get_template(): ?string
    {
        return $this->template;
    }
    public function with_template(string $template): self
    {
        $self = clone $this;
        $self->template = $template;
        return $self;
    }
    public function get_name(): ?string
    {
        return $this->name;
    }
    public function with_name(string $name): self
    {
        $self = clone $this;
        $self->name = $name;
        return $self;
    }
    public function get_short_name(): ?string
    {
        return $this->short_name;
    }
    public function with_short_name(string $short_name): self
    {
        $self = clone $this;
        $self->short_name = $short_name;
        return $self;
    }
    public function get_provider(): callable|string|null
    {
        return $this->provider;
    }
    public function with_provider(string|callable|null $provider): self
    {
        $self = clone $this;
        $self->provider = $provider;
        return $self;
    }
    public function get_processor(): callable|string|null
    {
        return $this->processor;
    }
    public function with_processor(string|callable|null $processor): self
    {
        $self = clone $this;
        $self->processor = $processor;
        return $self;
    }
    public function get_responder(): callable|string|null
    {
        return $this->responder;
    }
    public function with_responder(string|callable|null $responder): self
    {
        $self = clone $this;
        $self->responder = $responder;
        return $self;
    }
    public function get_repository(): callable|string|null
    {
        return $this->repository;
    }
    public function with_repository(string|callable|null $repository): self
    {
        $self = clone $this;
        $self->repository = $repository;
        return $self;
    }
    public function get_repository_method(): ?string
    {
        return $this->repository_method;
    }
    public function with_repository_method(string $repository_method): self
    {
        $self = clone $this;
        $self->repository_method = $repository_method;
        return $self;
    }
    public function get_repository_arguments(): ?array
    {
        return $this->repository_arguments;
    }
    public function with_repository_arguments(array $repository_arguments): self
    {
        $self = clone $this;
        $self->repository_arguments = $repository_arguments;
        return $self;
    }
    public function can_read(): ?bool
    {
        return $this->read;
    }
    public function with_read(bool $read): self
    {
        $self = clone $this;
        $self->read = $read;
        return $self;
    }
    public function can_write(): ?bool
    {
        return $this->write;
    }
    public function with_write(bool $write): self
    {
        $self = clone $this;
        $self->write = $write;
        return $self;
    }
    public function can_validate(): ?bool
    {
        return $this->validate;
    }
    public function with_validate(bool $validate): self
    {
        $self = clone $this;
        $self->validate = $validate;
        return $self;
    }
    public function can_deserialize(): ?bool
    {
        return $this->deserialize;
    }
    public function with_deserialize(bool $deserialize): self
    {
        $self = clone $this;
        $self->deserialize = $deserialize;
        return $self;
    }
    public function can_serialize(): ?bool
    {
        return $this->serialize;
    }
    public function with_serialize(bool $serialize): self
    {
        $self = clone $this;
        $self->serialize = $serialize;
        return $self;
    }
    public function get_form_type(): ?string
    {
        return $this->form_type;
    }
    public function with_form_type(string $form_type): self
    {
        $self = clone $this;
        $self->form_type = $form_type;
        return $self;
    }
    public function get_form_options(): ?array
    {
        return $this->form_options;
    }
    public function with_form_options(array $form_options): self
    {
        $self = clone $this;
        $self->form_options = $form_options;
        return $self;
    }
    public function get_normalization_context(): ?array
    {
        return $this->normalization_context;
    }
    public function with_normalization_context(?array $normalization_context): self
    {
        $self = clone $this;
        $self->normalization_context = $normalization_context;
        return $self;
    }
    public function get_denormalization_context(): ?array
    {
        return $this->denormalization_context;
    }
    public function with_denormalization_context(?array $denormalization_context): self
    {
        $self = clone $this;
        $self->denormalization_context = $denormalization_context;
        return $self;
    }
    public function get_validation_context(): ?array
    {
        return $this->validation_context;
    }
    public function with_validation_context(?array $validation_context): self
    {
        $self = clone $this;
        $self->validation_context = $validation_context;
        return $self;
    }
    public function get_event_short_name(): ?string
    {
        return $this->event_short_name;
    }
    public function with_event_short_name(string $event_short_name): self
    {
        $self = clone $this;
        $self->event_short_name = $event_short_name;
        return $self;
    }
    public function get_notification_message(): ?string
    {
        return $this->notification_message;
    }
    public function with_notification_message(string $notification_message): self
    {
        $self = clone $this;
        $self->notification_message = $notification_message;
        return $self;
    }
    public function get_security(): ?string
    {
        return $this->security instanceof \Stringable ? (string) $this->security : $this->security;
    }
    public function with_security(string|\Stringable|null $security): static
    {
        $self = clone $this;
        $self->security = $security;
        return $self;
    }
    public function get_security_message(): ?string
    {
        return $this->security_message;
    }
    public function with_security_message(?string $security_message): static
    {
        $self = clone $this;
        $self->security_message = $security_message;
        return $self;
    }
}