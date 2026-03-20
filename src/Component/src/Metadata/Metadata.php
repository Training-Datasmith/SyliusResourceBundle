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

use Doctrine\Inflector\Inflector as InflectorObject;
use Doctrine\Inflector\Inflector_Factory;
final class Metadata implements Metadata_Interface
{
    /** @var string */
    private $driver;
    private ?string $state_machine_component = null;
    /** @var string */
    private $templates_namespace;
    private array $parameters;
    private static ?Inflector_Object $inflector_instance = null;
    private function __construct(private readonly string $name, private readonly string $application_name, array $parameters)
    {
        $this->driver = $parameters['driver'];
        $this->templates_namespace = array_key_exists('templates', $parameters) ? $parameters['templates'] : null;
        $this->state_machine_component = $parameters['state_machine_component'] ?? null;
        $this->parameters = $parameters;
    }
    public static function from_alias_and_configuration(string $alias, array $parameters): self
    {
        [$application_name, $name] = self::parse_alias($alias);
        return new self($name, $application_name, $parameters);
    }
    public static function set_inflector(Inflector_Object $inflector): void
    {
        self::$inflector_instance = $inflector;
    }
    private static function get_inflector(): Inflector_Object
    {
        if (self::$inflector_instance === null) {
            $inflector_factory = Inflector_Factory::create();
            self::$inflector_instance = $inflector_factory->build();
        }
        return self::$inflector_instance;
    }
    public function get_alias(): string
    {
        return $this->application_name . '.' . $this->name;
    }
    public function get_application_name(): string
    {
        return $this->application_name;
    }
    public function get_name(): string
    {
        return $this->name;
    }
    public function get_humanized_name(): string
    {
        return strtolower(trim((string) preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $this->name)));
    }
    public function get_plural_name(): string
    {
        return self::get_inflector()->pluralize($this->name);
    }
    public function get_driver(): string|false
    {
        return $this->driver;
    }
    public function get_state_machine_component(): ?string
    {
        return $this->state_machine_component;
    }
    public function get_templates_namespace(): ?string
    {
        return $this->templates_namespace;
    }
    public function get_parameter(string $name)
    {
        if (!$this->has_parameter($name)) {
            throw new \InvalidArgumentException(sprintf('Parameter "%s" is not configured for resource "%s".', $name, $this->get_alias()));
        }
        return $this->parameters[$name];
    }
    public function has_parameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }
    public function get_parameters(): array
    {
        return $this->parameters;
    }
    public function get_class(string $name): string
    {
        if (!$this->has_class($name)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is not configured for resource "%s".', $name, $this->get_alias()));
        }
        return $this->parameters['classes'][$name];
    }
    public function has_class(string $name): bool
    {
        return isset($this->parameters['classes'][$name]);
    }
    public function get_service_id(string $service_name): string
    {
        return sprintf('%s.%s.%s', $this->application_name, $service_name, $this->name);
    }
    public function get_permission_code(string $permission_name): string
    {
        return sprintf('%s.%s.%s', $this->application_name, $this->name, $permission_name);
    }
    private static function parse_alias(string $alias): array
    {
        if (!str_contains($alias, '.')) {
            throw new \InvalidArgumentException(sprintf('Invalid alias "%s" supplied, it should conform to the following format "<applicationName>.<name>".', $alias));
        }
        return explode('.', $alias, 2);
    }
}
if (!class_exists(\Sylius\Component\Resource\Metadata\Metadata::class, false)) {
    class_alias(Metadata::class, \Sylius\Component\Resource\Metadata\Metadata::class);
}