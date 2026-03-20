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

final class Resource_Metadata
{
    private ?Operations $operations;
    /**
     * @param array<string, string>|null $routeRequirements
     */
    public function __construct(private ?string $alias = null, private ?string $section = null, private ?string $form_type = null, private ?string $templates_dir = null, private ?string $route_prefix = null, private ?array $route_requirements = null, private ?string $route_condition = null, private ?int $route_priority = null, private ?string $name = null, private ?string $plural_name = null, private ?string $application_name = null, private ?string $identifier = null, private ?array $normalization_context = null, private ?array $denormalization_context = null, private ?array $validation_context = null, private ?string $class = null, private string|false|null $driver = null, private ?array $vars = null, ?array $operations = null)
    {
        $this->operations = null === $operations ? null : new Operations($operations);
    }
    public function get_class(): ?string
    {
        return $this->class;
    }
    public function with_class(string $class): self
    {
        $self = clone $this;
        $self->class = $class;
        return $self;
    }
    public function get_alias(): ?string
    {
        return $this->alias;
    }
    public function with_alias(string $alias): self
    {
        $self = clone $this;
        $self->alias = $alias;
        return $self;
    }
    public function get_section(): ?string
    {
        return $this->section;
    }
    public function with_section(string $section): self
    {
        $self = clone $this;
        $self->section = $section;
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
    public function get_plural_name(): ?string
    {
        return $this->plural_name;
    }
    public function with_plural_name(string $plural_name): self
    {
        $self = clone $this;
        $self->plural_name = $plural_name;
        return $self;
    }
    public function get_application_name(): ?string
    {
        return $this->application_name;
    }
    public function with_application_name(string $application_name): self
    {
        $self = clone $this;
        $self->application_name = $application_name;
        return $self;
    }
    public function get_templates_dir(): ?string
    {
        return $this->templates_dir;
    }
    public function with_templates_dir(string $templates_dir): self
    {
        $self = clone $this;
        $self->templates_dir = $templates_dir;
        return $self;
    }
    public function get_route_prefix(): ?string
    {
        return $this->route_prefix;
    }
    public function with_route_prefix(string $route_prefix): self
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
    public function get_identifier(): ?string
    {
        return $this->identifier;
    }
    public function with_identifier(string $identifier): self
    {
        $self = clone $this;
        $self->identifier = $identifier;
        return $self;
    }
    public function has_operation(string $name): bool
    {
        return $this->operations?->has($name) ?? false;
    }
    public function get_operation(string $name): Operation
    {
        if (null === $operations = $this->operations) {
            throw new \RuntimeException(sprintf('No Operations were found on resource %s"', $this->alias ?? ''));
        }
        return $operations->get($name);
    }
    public function get_operations(): ?Operations
    {
        return $this->operations;
    }
    public function with_operations(Operations $operations): self
    {
        $self = clone $this;
        $self->operations = $operations;
        return $self;
    }
    public function get_route_name(string $short_name): string
    {
        $section = $this->get_section();
        $section_prefix = $section ? $section . '_' : '';
        return sprintf('%s_%s%s_%s', $this->get_application_name() ?? '', $section_prefix, $this->get_name() ?? '', $short_name);
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
    public function get_driver(): false|string|null
    {
        return $this->driver;
    }
    public function with_driver(false|string $driver): self
    {
        $self = clone $this;
        $self->driver = $driver;
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