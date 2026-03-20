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

use Sylius\Bundle\Resource_Bundle\Doctrine\ODM\Mongo_Db\Translatable_Repository;
use Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Model\Translatable_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Parameter;
use Symfony\Component\Dependency_Injection\Reference;
final class Doctrine_Odm_Driver extends Abstract_Doctrine_Driver
{
    public function get_type(): string
    {
        return Sylius_Resource_Bundle::DRIVER_DOCTRINE_MONGODB_ODM;
    }
    public function load(Container_Builder $container, Metadata_Interface $metadata): void
    {
        trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR will no longer be supported in 2.0.', self::class);
        parent::load($container, $metadata);
    }
    protected function add_repository(Container_Builder $container, Metadata_Interface $metadata): void
    {
        $model_class = $metadata->get_class('model');
        /** @var array $modelInterfaces */
        $model_interfaces = class_implements($model_class);
        $repository_class = in_array(Translatable_Interface::class, $model_interfaces) ? Translatable_Repository::class : new Parameter('sylius.mongodb.odm.repository.class');
        if ($metadata->has_class('repository')) {
            $repository_class = $metadata->get_class('repository');
        }
        $unit_of_work_definition = new Definition('Doctrine\ODM\MongoDB\UnitOfWork');
        $unit_of_work_definition->set_factory([new Reference($this->get_manager_service_id($metadata)), 'getUnitOfWork'])->set_public(false);
        $definition = new Definition($repository_class);
        $definition->set_arguments([new Reference($metadata->get_service_id('manager')), $unit_of_work_definition, $this->get_class_metadata_definition($metadata)]);
        $definition->add_tag('sylius.repository');
        $container->set_definition($metadata->get_service_id('repository'), $definition);
    }
    protected function get_manager_service_id(Metadata_Interface $metadata): string
    {
        if ($object_manager_name = $this->get_object_manager_name($metadata)) {
            return sprintf('doctrine_mongodb.odm.%s_document_manager', $object_manager_name);
        }
        return 'doctrine_mongodb.odm.document_manager';
    }
    protected function get_class_metadata_classname(): string
    {
        return 'Doctrine\ODM\MongoDB\Mapping\ClassMetadata';
    }
}