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

use Sylius\Resource\Doctrine\Common\Metadata\Resource\Factory\Doctrine_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Attributes_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Cached_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Event_Short_Name_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Factory_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Mutator_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Php_File_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Plural_Name_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Provider_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Redirect_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Resource_Metadata_Collection_Factory_Interface;
use Sylius\Resource\Metadata\Resource\Factory\State_Machine_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Templates_Dir_Resource_Metadata_Collection_Factory;
use Sylius\Resource\Metadata\Resource\Factory\Vars_Resource_Metadata_Collection_Factory;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.cache.metadata.resource_collection')->private()->parent('cache.system')->tag('cache.pool');
    $services->alias('sylius.resource_metadata_collection.factory', 'sylius.resource_metadata_collection.factory.attributes');
    $services->alias(Resource_Metadata_Collection_Factory_Interface::class, 'sylius.resource_metadata_collection.factory.attributes');
    $services->set('sylius.resource_metadata_collection.factory.attributes', Attributes_Resource_Metadata_Collection_Factory::class)->args([service('sylius.resource_registry'), service('sylius.routing.factory.operation_route_name_factory')]);
    $services->set('sylius.resource_metadata_collection.factory.php_file', Php_File_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory', null, 400)->args([service('sylius.resource_registry'), service('sylius.routing.factory.operation_route_name_factory'), service('sylius.metadata.resource_extractor.php_file'), service('.inner')]);
    $services->set('sylius.metadata.resource.metadata_collection_factory.mutator', Mutator_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory', null, 400)->args([service('sylius.metadata.mutator_collection.resource'), service('sylius.metadata.mutator_collection.operation'), service('.inner')]);
    $services->set('sylius.resource_metadata_collection.factory.plural_name', Plural_Name_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory', null, 300)->args([service('.inner'), service('sylius.metadata.inflector'), param('sylius.routing_path_bc_layer'), service('sylius.resource_registry')]);
    $services->set('sylius.resource_metadata_collection.factory.state_machine', State_Machine_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory', null, 300)->args([service('sylius.resource_registry'), service('.inner'), '%sylius.state_machine_component.default%']);
    $services->set('sylius.resource_metadata_collection.factory.doctrine', Doctrine_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory', null, 200)->args([service('sylius.resource_registry'), service('.inner')]);
    $services->set('sylius.resource_metadata_collection.factory.redirect', Redirect_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory', null, 200)->args([service('sylius.routing.factory.operation_route_name_factory'), service('.inner')]);
    $services->set('sylius.resource_metadata_collection.factory.vars', Vars_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory')->args([service('.inner')]);
    $services->set('sylius.resource_metadata_collection.factory.provider', Provider_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory')->args([service('.inner')]);
    $services->set('sylius.resource_metadata_collection.factory.resource_factory', Factory_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory')->args([service('sylius.resource_registry'), service('.inner')]);
    $services->set('sylius.resource_metadata_collection.factory.event_short_name', Event_Short_Name_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory')->args([service('.inner')]);
    $services->set('sylius.resource_metadata_collection.factory.templates_dir', Templates_Dir_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory')->args([service('.inner'), '%sylius.resource.settings%']);
    $services->set('sylius.resource_metadata_collection.factory.cached', Cached_Resource_Metadata_Collection_Factory::class)->decorate('sylius.resource_metadata_collection.factory', null, -10)->args([service('sylius.cache.metadata.resource_collection'), service('.inner')]);
};