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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection;

use Behat\Transliterator\Transliterator;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Controller;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver\Doctrine\Doctrine_Odm_Driver;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver\Doctrine\Doctrine_Orm_Driver;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver\Doctrine\Doctrine_Phpcr_Driver;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver\Driver_Provider;
use Sylius\Bundle\Resource_Bundle\Form\Type\Default_Resource_Type;
use Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle;
use Sylius\Component\Resource\Factory\Factory_Interface as LegacyFactoryInterface;
use Sylius\Resource\Factory\Factory;
use Sylius\Resource\Factory\Factory_Interface;
use Sylius\Resource\Metadata\As_Operation_Mutator;
use Sylius\Resource\Metadata\As_Resource;
use Sylius\Resource\Metadata\As_Resource_Mutator;
use Sylius\Resource\Metadata\Metadata;
use Sylius\Resource\Metadata\Operation_Mutator_Interface;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Metadata\Resource_Mutator_Interface;
use Sylius\Resource\Reflection\Class_Reflection;
use Sylius\Resource\Reflection\Reflection_Class_Recursive_Iterator;
use Sylius\Resource\State\Processor_Interface;
use Sylius\Resource\State\Provider_Interface;
use Sylius\Resource\State\Responder_Interface;
use Sylius\Resource\Twig\Context\Factory\Context_Factory_Interface;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Config\Loader\Loader_Interface;
use Symfony\Component\Config\Resource\Directory_Resource;
use Symfony\Component\Dependency_Injection\Child_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Exception\InvalidArgumentException;
use Symfony\Component\Dependency_Injection\Exception\RuntimeException;
use Symfony\Component\Dependency_Injection\Extension\Extension;
use Symfony\Component\Dependency_Injection\Extension\Prepend_Extension_Interface;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
use Symfony\Component\Finder\Finder;
use function Symfony\Component\String\u;
final class Sylius_Resource_Extension extends Extension implements Prepend_Extension_Interface
{
    public function load(array $configs, Container_Builder $container): void
    {
        $config = $this->process_configuration($this->get_configuration([], $container), $configs);
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');
        /** @var array<string, string> $bundles */
        $bundles = $container->get_parameter('kernel.bundles');
        if (array_key_exists('SyliusGridBundle', $bundles)) {
            $loader->load('services/integrations/grid.php');
        }
        if ($config['translation']['enabled']) {
            $loader->load('services/integrations/translation.php');
            $container->set_alias('sylius.translation_locale_provider', $config['translation']['locale_provider'])->set_public(true);
        }
        $container->set_parameter('sylius.resource.mapping', $config['mapping']);
        $container->set_parameter('sylius.resource.settings', $config['settings']);
        $routing_path_bc_layer = $config['routing_path_bc_layer'] ?? null;
        if (null === $routing_path_bc_layer) {
            $routing_path_bc_layer = class_exists(Transliterator::class);
        }
        if ($routing_path_bc_layer && !class_exists(Transliterator::class)) {
            throw new RuntimeException(sprintf('The routing path bc-layer is enabled but behat/transliterator package is not installed.'));
        }
        $container->set_parameter('sylius.routing_path_bc_layer', $routing_path_bc_layer);
        $container->set_alias('sylius.resource_controller.authorization_checker', $config['authorization_checker']);
        $container->set_alias('sylius.path_segment_name_generator', $config['path_segment_name_generator']);
        $this->register_metadata_configuration($container, $config);
        $this->auto_register_resources($config, $container);
        $this->load_persistence($config['drivers'], $config['resources'], $loader, $container);
        $this->load_resources($config['resources'], $container);
        $container->register_attribute_for_autoconfiguration(As_Resource::class, static function (Child_Definition $definition): void {
            $definition->add_tag('container.excluded', ['source' => 'by #[AsResource] attribute']);
        });
        $container->register_attribute_for_autoconfiguration(As_Resource_Mutator::class, static function (Child_Definition $definition, As_Resource_Mutator $attribute, \ReflectionClass $reflector): void {
            if (!is_a($reflector->name, Resource_Mutator_Interface::class, true)) {
                throw new RuntimeException(\sprintf('Resource mutator "%s" should implement %s', $reflector->name, Resource_Mutator_Interface::class));
            }
            $definition->add_tag('sylius.resource_mutator', ['resourceClass' => $attribute->resource_class]);
        });
        $container->register_attribute_for_autoconfiguration(As_Operation_Mutator::class, static function (Child_Definition $definition, As_Operation_Mutator $attribute, \ReflectionClass $reflector): void {
            if (!is_a($reflector->name, Operation_Mutator_Interface::class, true)) {
                throw new RuntimeException(\sprintf('Operation mutator "%s" should implement %s', $reflector->name, Operation_Mutator_Interface::class));
            }
            $definition->add_tag('sylius.operation_mutator', ['operationName' => $attribute->operation_name]);
        });
        $container->register_for_autoconfiguration(Provider_Interface::class)->add_tag('sylius.state_provider');
        $container->register_for_autoconfiguration(Processor_Interface::class)->add_tag('sylius.state_processor');
        $container->register_for_autoconfiguration(Responder_Interface::class)->add_tag('sylius.state_responder');
        $container->register_for_autoconfiguration(Factory_Interface::class)->add_tag('sylius.resource_factory');
        $container->register_for_autoconfiguration(Legacy_Factory_Interface::class)->add_tag('sylius.resource_factory');
        $container->register_for_autoconfiguration(Context_Factory_Interface::class)->add_tag('sylius.twig_context_factory');
        $container->add_object_resource(Metadata::class);
        $container->add_object_resource(Driver_Provider::class);
        $container->add_object_resource(Doctrine_Orm_Driver::class);
        $container->add_object_resource(Doctrine_Odm_Driver::class);
        $container->add_object_resource(Doctrine_Phpcr_Driver::class);
    }
    public function get_configuration(array $config, Container_Builder $container): Configuration
    {
        $configuration = new Configuration();
        $container->add_object_resource($configuration);
        return $configuration;
    }
    public function prepend(Container_Builder $container): void
    {
        $config = ['body_listener' => ['enabled' => true]];
        $container->prepend_extension_config('fos_rest', $config);
    }
    private function auto_register_resources(array &$config, Container_Builder $container): void
    {
        /** @var array $resources */
        $resources = $config['resources'];
        /** @var array $mapping */
        $mapping = $container->get_parameter('sylius.resource.mapping');
        $paths = $mapping['paths'] ?? [];
        foreach (Reflection_Class_Recursive_Iterator::get_reflection_classes_from_directories($paths) as $reflection_class) {
            $class_name = $reflection_class->get_name();
            $resource_attributes = Class_Reflection::get_class_attributes($class_name, As_Resource::class);
            foreach ($resource_attributes as $resource_attribute) {
                /** @var AsResource $resource */
                $resource = $resource_attribute->new_instance();
                $resource_metadata = $resource->to_metadata();
                $resource_alias = $this->get_resource_alias($resource_metadata, $class_name);
                if ($resources[$resource_alias] ?? false) {
                    continue;
                }
                $resources[$resource_alias] = ['classes' => ['model' => $class_name, 'controller' => Resource_Controller::class, 'factory' => Factory::class, 'form' => Default_Resource_Type::class], 'driver' => $resource_metadata->get_driver() ?? Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM];
            }
        }
        $config['resources'] = $resources;
    }
    /** @param class-string $className */
    private function get_resource_alias(Resource_Metadata $resource, string $class_name): string
    {
        $alias = $resource->get_alias();
        $application_name = $resource->get_application_name() ?? 'app';
        if (null !== $alias) {
            return $alias;
        }
        $reflection_class = new \ReflectionClass($class_name);
        $short_name = $reflection_class->get_short_name();
        $suffix = 'Resource';
        if (str_ends_with($short_name, $suffix)) {
            $short_name = substr($short_name, 0, strlen($short_name) - strlen($suffix));
        }
        return u($application_name)->snake()->to_string() . '.' . u($short_name)->snake()->to_string();
    }
    /**
     * @param array<string, array{driver?: string|false}> $resources
     */
    private function load_persistence(array $drivers, array $resources, Loader_Interface $loader, Container_Builder $container): void
    {
        $available_drivers = $this->get_available_drivers($container);
        // Enable all available drivers if there is no configured drivers
        $drivers = [] !== $drivers ? $drivers : $available_drivers;
        $resource_drivers = $this->get_resource_drivers($resources);
        $this->check_configured_drivers($drivers, $available_drivers, $resource_drivers);
        $integrate_doctrine = array_reduce($drivers, fn(bool $result, string $driver): bool => $result || in_array($driver, [Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM, Sylius_Resource_Bundle::DRIVER_DOCTRINE_PHPCR_ODM, Sylius_Resource_Bundle::DRIVER_DOCTRINE_MONGODB_ODM], true), false);
        if ($integrate_doctrine) {
            $loader->load('services/integrations/doctrine.php');
        }
        foreach ($drivers as $driver) {
            if (in_array($driver, [Sylius_Resource_Bundle::DRIVER_DOCTRINE_PHPCR_ODM, Sylius_Resource_Bundle::DRIVER_DOCTRINE_MONGODB_ODM], true)) {
                trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" driver is deprecated. Doctrine MongoDB and PHPCR will no longer be supported in 2.0.', $driver);
            }
            // Only Doctrine drivers need service integration file
            if (!str_starts_with((string) $driver, 'doctrine')) {
                continue;
            }
            $loader->load(sprintf('services/integrations/%s.php', $driver));
        }
    }
    /**
     * @param array<string, array{driver?: string|false}> $resources
     *
     * @return array<string, string>
     */
    private function get_resource_drivers(array $resources): array
    {
        $resource_drivers = array_map(fn(array $resource): string|false => $resource['driver'] ?? false, $resources);
        // Remove resources with disabled driver
        return array_filter($resource_drivers, fn(string|false $driver): bool => false !== $driver);
    }
    private function get_available_drivers(Container_Builder $container): array
    {
        $available_drivers = [];
        if ($container::will_be_available(Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM, \Doctrine\ORM\Entity_Manager_Interface::class, ['doctrine/doctrine-bundle'])) {
            $available_drivers[] = Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM;
        }
        if ($container::will_be_available(Sylius_Resource_Bundle::DRIVER_DOCTRINE_PHPCR_ODM, \Doctrine\ODM\PHPCR\Document\Resource::class, ['doctrine/doctrine-bundle'])) {
            $available_drivers[] = Sylius_Resource_Bundle::DRIVER_DOCTRINE_PHPCR_ODM;
        }
        if ($container::will_be_available(Sylius_Resource_Bundle::DRIVER_DOCTRINE_MONGODB_ODM, \Doctrine\ODM\Mongo_Db\Document_Manager::class, ['doctrine/doctrine-bundle'])) {
            $available_drivers[] = Sylius_Resource_Bundle::DRIVER_DOCTRINE_MONGODB_ODM;
        }
        return $available_drivers;
    }
    /**
     * @param string[] $configuredDrivers
     * @param string[] $availableDrivers
     * @param array<string, string> $resourceDrivers
     */
    private function check_configured_drivers(array $configured_drivers, array $available_drivers, array $resource_drivers): void
    {
        foreach ($configured_drivers as $driver) {
            if (!in_array($driver, $available_drivers, true)) {
                throw new InvalidArgumentException(sprintf('Driver "%s" is configured, but this driver is not available. Try running "composer require %s"', $driver, $driver));
            }
        }
        foreach ($resource_drivers as $resource => $driver) {
            if (!in_array($driver, $available_drivers, true)) {
                throw new InvalidArgumentException(sprintf('Resource "%s" uses drivers "%s", but this driver is not available. Try running "composer require %s"', $resource, $driver, $driver));
            }
            if (!in_array($driver, $configured_drivers, true)) {
                throw new InvalidArgumentException(sprintf('Resource "%s" uses drivers "%s", but this driver is not enabled. Try adding "%s" in sylius_resource.drivers option', $resource, $driver, $driver));
            }
        }
    }
    private function load_resources(array $loaded_resources, Container_Builder $container): void
    {
        /** @var array<string, array> $resources */
        $resources = $container->has_parameter('sylius.resources') ? $container->get_parameter('sylius.resources') : [];
        foreach ($loaded_resources as $alias => $resource_config) {
            $metadata = Metadata::from_alias_and_configuration($alias, $resource_config);
            $resources[$alias] = $resource_config;
            $container->set_parameter('sylius.resources', $resources);
            if ($metadata->get_driver()) {
                Driver_Provider::get($metadata)->load($container, $metadata);
            }
            if ($metadata->has_parameter('translation')) {
                $alias .= '_translation';
                $resource_config = array_merge(['driver' => $resource_config['driver']], $resource_config['translation']);
                $resources[$alias] = $resource_config;
                $container->set_parameter('sylius.resources', $resources);
                $metadata = Metadata::from_alias_and_configuration($alias, $resource_config);
                if ($metadata->get_driver()) {
                    Driver_Provider::get($metadata)->load($container, $metadata);
                }
            }
        }
    }
    private function register_metadata_configuration(Container_Builder $container, array $config): void
    {
        $resources = $this->get_resource_files_to_watch($container, $config);
        $container->get_definition('sylius.metadata.resource_extractor.php_file')->replace_argument(0, $resources);
    }
    private function get_resource_files_to_watch(Container_Builder $container, array $config): array
    {
        $files = [];
        /** @var string $path */
        foreach ($config['mapping']['imports'] ?? [] as $path) {
            if (is_dir($path)) {
                foreach (Finder::create()->follow_links()->files()->in($path)->name('/\.php$/')->sort_by_name() as $file) {
                    $files[] = $file->get_real_path();
                }
                $container->add_resource(new Directory_Resource($path, '/\.php$/'));
                continue;
            }
            if ($container->file_exists($path, false)) {
                if (!str_ends_with($path, '.php')) {
                    throw new RuntimeException(\sprintf('Unsupported mapping type in "%s", supported type is PHP.', $path));
                }
                $files[] = $path;
                continue;
            }
            throw new RuntimeException(\sprintf('Could not open file or directory "%s".', $path));
        }
        return $files;
    }
}