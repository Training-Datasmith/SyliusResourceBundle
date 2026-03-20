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

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class As_Resource
{
    /**
     * @param array<string, string>|null $routeRequirements
     */
    public function __construct(private ?string $alias = null, private ?string $section = null, private ?string $form_type = null, private ?string $templates_dir = null, private ?string $route_prefix = null, private ?array $route_requirements = null, private ?string $route_condition = null, private ?int $route_priority = null, private ?string $name = null, private ?string $plural_name = null, private ?string $application_name = null, private ?string $identifier = null, private ?array $normalization_context = null, private ?array $denormalization_context = null, private ?array $validation_context = null, private ?string $class = null, private string|false|null $driver = null, private ?array $vars = null, private ?array $operations = null)
    {
    }
    public function to_metadata(): Resource_Metadata
    {
        return new Resource_Metadata(alias: $this->alias, section: $this->section, formType: $this->form_type, templatesDir: $this->templates_dir, routePrefix: $this->route_prefix, routeRequirements: $this->route_requirements, routeCondition: $this->route_condition, routePriority: $this->route_priority, name: $this->name, pluralName: $this->plural_name, applicationName: $this->application_name, identifier: $this->identifier, normalizationContext: $this->normalization_context, denormalizationContext: $this->denormalization_context, validationContext: $this->validation_context, class: $this->class, driver: $this->driver, vars: $this->vars, operations: $this->operations);
    }
}