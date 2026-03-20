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

use Sylius\Bundle\Resource_Bundle\Doctrine\ODM\Mongo_Db\Document_Repository;
use Sylius\Bundle\Resource_Bundle\Event_Listener\Odm_Mapped_Super_Class_Subscriber;
use Sylius\Bundle\Resource_Bundle\Event_Listener\Odm_Repository_Class_Subscriber;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.mongodb_odm.repository.class', Document_Repository::class);
    $services->defaults()->public();
    $services->set('sylius.event_subscriber.odm_mapped_super_class', Odm_Mapped_Super_Class_Subscriber::class)->args([service('sylius.resource_registry')])->tag('doctrine_mongodb.odm.event_subscriber', ['priority' => 8192])->deprecate('sylius/resource-bundle', '1.3', 'The "%service_id%" service is deprecated since sylius/resource-bundle 1.3. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.');
    $services->set('sylius.event_subscriber.odm_repository_class', Odm_Repository_Class_Subscriber::class)->args([service('sylius.resource_registry')])->tag('doctrine_mongodb.odm.event_subscriber', ['priority' => 8192])->deprecate('sylius/resource-bundle', '1.3', 'The "%service_id%" service is deprecated since sylius/resource-bundle 1.3. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.');
};