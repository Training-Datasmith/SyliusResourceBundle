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
namespace Symfony\Component\Dependency_Injection\Loader\Configurator;

use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Container_Repository_Factory;
use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Entity_Repository;
use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Form\Builder\Default_Form_Builder;
use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Form\Builder\Default_Form_Builder as DefaultFormBuilderInterface;
use Sylius\Bundle\Resource_Bundle\Event_Listener\Orm_Mapped_Super_Class_Subscriber;
use Sylius\Bundle\Resource_Bundle\Event_Listener\Orm_Repository_Class_Subscriber;
use Sylius\Bundle\Resource_Bundle\Event_Listener\Orm_Translatable_Listener as TranslatableListener;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.orm.repository.class', Entity_Repository::class);
    $parameters->set('sylius.translation.translatable_listener.doctrine.orm.class', Translatable_Listener::class);
    $services->defaults()->public();
    $services->set('sylius.event_subscriber.orm_mapped_super_class', Orm_Mapped_Super_Class_Subscriber::class)->args([service('sylius.resource_registry')])->tag('doctrine.event_listener', ['event' => 'loadClassMetadata', 'priority' => 8192]);
    $services->alias(Orm_Mapped_Super_Class_Subscriber::class, 'sylius.event_subscriber.orm_mapped_super_class');
    $services->set('sylius.event_subscriber.orm_repository_class', Orm_Repository_Class_Subscriber::class)->args([service('sylius.resource_registry')])->tag('doctrine.event_listener', ['event' => 'loadClassMetadata', 'priority' => 8192]);
    $services->alias(Orm_Repository_Class_Subscriber::class, 'sylius.event_subscriber.orm_repository_class');
    $services->set('sylius.form_builder.default', Default_Form_Builder::class)->private()->args([service('doctrine.orm.default_entity_manager')])->tag('sylius.default_resource_form.builder', ['type' => 'doctrine/orm']);
    $services->alias(Default_Form_Builder_Interface::class, 'sylius.form_builder.default')->private();
    $services->set('sylius.doctrine.orm.container_repository_factory', Container_Repository_Factory::class)->private()->decorate('doctrine.orm.container_repository_factory')->args([service('sylius.doctrine.orm.container_repository_factory.inner'), '%sylius.doctrine.orm.container_repository_factory.entities%']);
    $services->alias(Container_Repository_Factory::class, 'sylius.doctrine.orm.container_repository_factory')->private();
};