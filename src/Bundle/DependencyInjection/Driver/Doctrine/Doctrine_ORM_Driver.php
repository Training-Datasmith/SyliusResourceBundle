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

use Doctrine\Bundle\Doctrine_Bundle\Dependency_Injection\Compiler\Service_Repository_Compiler_Pass;
use Doctrine\Bundle\Doctrine_Bundle\Repository\Service_Entity_Repository;
use Doctrine\Common\Persistence\Object_Manager as DeprecatedObjectManager;
use Doctrine\ORM\Entity_Manager_Interface;
use Doctrine\ORM\Mapping\Class_Metadata;
use Doctrine\Persistence\Object_Manager;
use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Entity_Repository;
use Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle;
use Sylius\Component\Resource\Repository\Repository_Interface as LegacyRepositoryInterface;
use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final class Doctrine_Orm_Driver extends Abstract_Doctrine_Driver
{
    public const GENERIC_ENTITIES_PARAMETER = 'sylius.doctrine.orm.container_repository_factory.entities';
    public function get_type(): string
    {
        return Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM;
    }
    protected function add_repository(Container_Builder $container, Metadata_Interface $metadata): void
    {
        $repository_class_parameter_name = sprintf('%s.repository.%s.class', $metadata->get_application_name(), $metadata->get_name());
        $repository_class = Entity_Repository::class;
        /** @var string[] $genericEntities */
        $generic_entities = $container->has_parameter(self::GENERIC_ENTITIES_PARAMETER) ? $container->get_parameter(self::GENERIC_ENTITIES_PARAMETER) : [];
        if ($container->has_parameter($repository_class_parameter_name)) {
            /** @var string $repositoryClass */
            $repository_class = $container->get_parameter($repository_class_parameter_name);
        }
        if ($metadata->has_class('repository')) {
            /** @var string $repositoryClass */
            $repository_class = $metadata->get_class('repository');
        }
        $service_id = $metadata->get_service_id('repository');
        $manager_reference = new Reference($metadata->get_service_id('manager'));
        $definition = new Definition($repository_class);
        $definition->set_public(true);
        $definition->add_tag('sylius.repository');
        if ($repository_class === Entity_Repository::class) {
            /** @var string $entityClass */
            $entity_class = $metadata->get_class('model');
            $definition->set_factory([$manager_reference, 'getRepository']);
            $definition->set_arguments([$entity_class]);
            $container->set_definition($service_id, $definition);
            $generic_entities[] = $entity_class;
        } else {
            if (is_a($repository_class, Service_Entity_Repository::class, true)) {
                $definition->set_arguments([new Reference('doctrine')]);
                $container->set_definition($service_id, $definition);
            } else {
                $definition->set_arguments([$manager_reference, $this->get_class_metadata_definition($metadata)]);
            }
            $container->set_definition($service_id, $definition);
            $doctrine_definition = new Definition($repository_class);
            $doctrine_definition->add_tag(Service_Repository_Compiler_Pass::REPOSITORY_SERVICE_TAG);
            $doctrine_definition->set_factory([new Reference('service_container'), 'get']);
            $doctrine_definition->set_arguments([$service_id]);
            $container->set_definition($repository_class, $doctrine_definition);
        }
        /** @var array $repositoryInterfaces */
        $repository_interfaces = class_implements($repository_class);
        /** @var array $repositoryParents */
        $repository_parents = class_parents($repository_class);
        $typehint_classes = array_merge($repository_interfaces, [$repository_class, Legacy_Repository_Interface::class], $repository_parents);
        foreach ($typehint_classes as $typehint_class) {
            $container->register_alias_for_argument($metadata->get_service_id('repository'), $typehint_class, $metadata->get_humanized_name() . ' repository');
        }
        $container->set_parameter(self::GENERIC_ENTITIES_PARAMETER, $generic_entities);
    }
    protected function add_manager(Container_Builder $container, Metadata_Interface $metadata): void
    {
        parent::add_manager($container, $metadata);
        $typehint_classes = [Deprecated_Object_Manager::class, Object_Manager::class, Entity_Manager_Interface::class];
        foreach ($typehint_classes as $typehint_class) {
            $container->register_alias_for_argument($metadata->get_service_id('manager'), $typehint_class, $metadata->get_humanized_name() . ' manager');
        }
    }
    protected function get_manager_service_id(Metadata_Interface $metadata): string
    {
        if ($object_manager_name = $this->get_object_manager_name($metadata)) {
            return sprintf('doctrine.orm.%s_entity_manager', $object_manager_name);
        }
        return 'doctrine.orm.entity_manager';
    }
    protected function get_class_metadata_classname(): string
    {
        return Class_Metadata::class;
    }
}