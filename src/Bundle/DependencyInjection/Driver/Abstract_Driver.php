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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver;

use Sylius\Component\Resource\Factory\Factory_Interface as LegacyFactoryInterface;
use Sylius\Component\Resource\Factory\Translatable_Factory_Interface as LegacyTranslatableFactoryInterface;
use Sylius\Resource\Factory\Factory;
use Sylius\Resource\Factory\Translatable_Factory_Interface;
use Sylius\Resource\Metadata\Metadata;
use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Container_Interface;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
abstract class Abstract_Driver implements Driver_Interface
{
    public function load(Container_Builder $container, Metadata_Interface $metadata): void
    {
        $this->set_classes_parameters($container, $metadata);
        if ($metadata->has_class('controller')) {
            $this->add_controller($container, $metadata);
        }
        $this->add_manager($container, $metadata);
        $this->add_repository($container, $metadata);
        if ($metadata->has_class('factory')) {
            $this->add_factory($container, $metadata);
        }
    }
    protected function set_classes_parameters(Container_Builder $container, Metadata_Interface $metadata): void
    {
        if ($metadata->has_class('model')) {
            $container->set_parameter(sprintf('%s.model.%s.class', $metadata->get_application_name(), $metadata->get_name()), $metadata->get_class('model'));
        }
        if ($metadata->has_class('controller')) {
            $container->set_parameter(sprintf('%s.controller.%s.class', $metadata->get_application_name(), $metadata->get_name()), $metadata->get_class('controller'));
        }
        if ($metadata->has_class('factory')) {
            $container->set_parameter(sprintf('%s.factory.%s.class', $metadata->get_application_name(), $metadata->get_name()), $metadata->get_class('factory'));
        }
        if ($metadata->has_class('repository')) {
            $container->set_parameter(sprintf('%s.repository.%s.class', $metadata->get_application_name(), $metadata->get_name()), $metadata->get_class('repository'));
        }
        if ($metadata->has_class('form')) {
            $container->set_parameter(sprintf('%s.form.%s.class', $metadata->get_application_name(), $metadata->get_name()), $metadata->get_class('form'));
        }
    }
    protected function add_controller(Container_Builder $container, Metadata_Interface $metadata): void
    {
        $definition = new Definition($metadata->get_class('controller'));
        $definition->set_public(true)->set_arguments([$this->get_metadata_definition($metadata), new Reference('sylius.resource_controller.request_configuration_factory'), new Reference('sylius.resource_controller.view_handler', Container_Interface::NULL_ON_INVALID_REFERENCE), new Reference($metadata->get_service_id('repository')), new Reference($metadata->get_service_id('factory')), new Reference('sylius.resource_controller.new_resource_factory'), new Reference($metadata->get_service_id('manager')), new Reference('sylius.resource_controller.single_resource_provider'), new Reference('sylius.resource_controller.resources_collection_provider'), new Reference('sylius.resource_controller.form_factory'), new Reference('sylius.resource_controller.redirect_handler'), new Reference('sylius.resource_controller.flash_helper'), new Reference('sylius.resource_controller.authorization_checker'), new Reference('sylius.resource_controller.event_dispatcher'), new Reference($metadata->get_service_id('controller_state_machine'), Container_Interface::NULL_ON_INVALID_REFERENCE), new Reference('sylius.resource_controller.resource_update_handler'), new Reference('sylius.resource_controller.resource_delete_handler')])->add_method_call('setContainer', [new Reference('service_container')])->add_tag('controller.service_arguments');
        $container->set_definition($metadata->get_service_id('controller'), $definition);
    }
    protected function add_factory(Container_Builder $container, Metadata_Interface $metadata): void
    {
        $factory_class = $metadata->get_class('factory');
        $model_class = $metadata->get_class('model');
        $definition = new Definition($factory_class);
        $definition->set_public(true);
        $definition_args = [$model_class];
        /** @var array $factoryInterfaces */
        $factory_interfaces = class_implements($factory_class);
        if (in_array(Translatable_Factory_Interface::class, $factory_interfaces, true)) {
            $decorated_definition = new Definition(Factory::class);
            $decorated_definition->set_arguments($definition_args);
            $definition_args = [$decorated_definition, new Reference('sylius.translation_locale_provider')];
        }
        $definition->set_arguments($definition_args);
        $container->set_definition($metadata->get_service_id('factory'), $definition)->add_tag('sylius.resource_factory');
        /** @var array $factoryParents */
        $factory_parents = class_parents($factory_class);
        $typehint_classes = array_merge($factory_interfaces, [$factory_class, Legacy_Factory_Interface::class], $factory_parents);
        if (in_array(Translatable_Factory_Interface::class, $factory_interfaces, true)) {
            $typehint_classes[] = Legacy_Translatable_Factory_Interface::class;
        }
        foreach ($typehint_classes as $typehint_class) {
            $container->register_alias_for_argument($metadata->get_service_id('factory'), $typehint_class, $metadata->get_humanized_name() . ' factory');
        }
    }
    protected function get_metadata_definition(Metadata_Interface $metadata): Definition
    {
        $definition = new Definition(Metadata::class);
        $definition->set_factory([new Reference('sylius.resource_registry'), 'get'])->set_arguments([$metadata->get_alias()]);
        return $definition;
    }
    abstract protected function add_manager(Container_Builder $container, Metadata_Interface $metadata): void;
    abstract protected function add_repository(Container_Builder $container, Metadata_Interface $metadata): void;
}