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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver\Doctrine;

use Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR\Event_Listener\Default_Parent_Listener;
use Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR\Event_Listener\Name_Filter_Listener;
use Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR\Event_Listener\Name_Resolver_Listener;
use Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle;
use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Exception\InvalidArgumentException;
use Symfony\Component\Dependency_Injection\Parameter;
use Symfony\Component\Dependency_Injection\Reference;
final class Doctrine_Phpcr_Driver extends Abstract_Doctrine_Driver
{
    public function load(Container_Builder $container, Metadata_Interface $metadata): void
    {
        trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR will no longer be supported in 2.0.', self::class);
        parent::load($container, $metadata);
        $this->add_resource_listeners($container, $metadata);
    }
    protected function add_resource_listeners(Container_Builder $container, Metadata_Interface $metadata): void
    {
        $default_options = [
            // if no parent is given default to the parent path given here.
            'parent_path_default' => null,
            // auto-create the parent path if it does not exist.
            'parent_path_autocreate' => false,
            // set true to always override the parent path.
            'parent_path_force' => false,
            // automatically replace invalid characters in the node name
            // with a blank space.
            'name_filter' => true,
            // automatically resolve same-name-sibling conflicts.
            'name_resolver' => true,
        ];
        $metadata_options = $metadata->has_parameter('options') ? $metadata->get_parameter('options') : [];
        if ($diff = array_diff(array_keys($metadata_options), array_keys($default_options))) {
            throw new InvalidArgumentException(sprintf('Unknown PHPCR-ODM configuration options: "%s"', implode('", "', $diff)));
        }
        $options = array_merge($default_options, $metadata_options);
        $create_event_name = sprintf('%s.%s.pre_%s', $metadata->get_application_name(), $metadata->get_name(), 'create');
        $update_event_name = sprintf('%s.%s.pre_%s', $metadata->get_application_name(), $metadata->get_name(), 'update');
        if ($options['parent_path_default']) {
            $default_path = new Definition(Default_Parent_Listener::class);
            $default_path->set_arguments([new Reference($metadata->get_service_id('manager')), $options['parent_path_default'], $options['parent_path_autocreate'], $options['parent_path_force']]);
            $default_path->add_tag('kernel.event_listener', ['event' => $create_event_name, 'method' => 'onPreCreate']);
            $container->set_definition(sprintf('%s.resource.%s.doctrine.odm.phpcr.event_listener.default_path', $metadata->get_application_name(), $metadata->get_name()), $default_path);
        }
        if ($options['name_filter']) {
            $name_filter = new Definition(Name_Filter_Listener::class);
            $name_filter->set_arguments([new Reference($metadata->get_service_id('manager'))]);
            $name_filter->add_tag('kernel.event_listener', ['event' => $create_event_name, 'method' => 'onEvent']);
            $name_filter->add_tag('kernel.event_listener', ['event' => $update_event_name, 'method' => 'onEvent']);
            $container->set_definition(sprintf('%s.resource.%s.doctrine.odm.phpcr.event_listener.name_filter', $metadata->get_application_name(), $metadata->get_name()), $name_filter);
        }
        if ($options['name_resolver']) {
            $name_resolver = new Definition(Name_Resolver_Listener::class);
            $name_resolver->set_arguments([new Reference($metadata->get_service_id('manager'))]);
            $name_resolver->add_tag('kernel.event_listener', ['event' => $create_event_name, 'method' => 'onEvent']);
            $name_resolver->add_tag('kernel.event_listener', ['event' => $update_event_name, 'method' => 'onEvent']);
            $container->set_definition(sprintf('%s.resource.%s.doctrine.odm.phpcr.event_listener.name_resolver', $metadata->get_application_name(), $metadata->get_name()), $name_resolver);
        }
    }
    public function get_type(): string
    {
        return Sylius_Resource_Bundle::DRIVER_DOCTRINE_PHPCR_ODM;
    }
    protected function add_repository(Container_Builder $container, Metadata_Interface $metadata): void
    {
        $repository_class = new Parameter('sylius.phpcr_odm.repository.class');
        if ($metadata->has_class('repository')) {
            $repository_class = $metadata->get_class('repository');
        }
        $definition = new Definition($repository_class);
        $definition->set_arguments([new Reference($metadata->get_service_id('manager')), $this->get_class_metadata_definition($metadata)]);
        $definition->add_tag('sylius.repository');
        $container->set_definition($metadata->get_service_id('repository'), $definition);
    }
    protected function get_manager_service_id(Metadata_Interface $metadata): string
    {
        if ($object_manager_name = $this->get_object_manager_name($metadata)) {
            return sprintf('doctrine_phpcr.odm.%s_document_manager', $object_manager_name);
        }
        return 'doctrine_phpcr.odm.document_manager';
    }
    protected function get_class_metadata_classname(): string
    {
        return 'Doctrine\ODM\PHPCR\Mapping\ClassMetadata';
    }
}