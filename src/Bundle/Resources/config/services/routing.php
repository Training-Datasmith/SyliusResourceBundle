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

use Sylius\Bundle\Resource_Bundle\Routing\Crud_Routes_Attributes_Loader;
use Sylius\Bundle\Resource_Bundle\Routing\Resource_Loader;
use Sylius\Bundle\Resource_Bundle\Routing\Route_Attributes_Factory;
use Sylius\Bundle\Resource_Bundle\Routing\Route_Attributes_Factory_Interface;
use Sylius\Bundle\Resource_Bundle\Routing\Route_Factory;
use Sylius\Bundle\Resource_Bundle\Routing\Routes_Attributes_Loader;
use Sylius\Resource\Symfony\Routing\Factory\Attributes_Operation_Route_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Attributes_Operation_Route_Factory_Interface;
use Sylius\Resource\Symfony\Routing\Factory\Operation_Route_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Operation_Route_Factory_Interface;
use Sylius\Resource\Symfony\Routing\Factory\Route_Name\Operation_Route_Name_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Route_Name\Operation_Route_Name_Factory_Interface;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Bulk_Operation_Route_Path_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Collection_Operation_Route_Path_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Create_Operation_Route_Path_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Delete_Operation_Route_Path_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Operation_Route_Path_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Operation_Route_Path_Factory_Interface;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Show_Operation_Route_Path_Factory;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Update_Operation_Route_Path_Factory;
use Sylius\Resource\Symfony\Routing\Redirect_Handler;
use Sylius\Resource\Symfony\Routing\Redirect_Handler_Interface;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $container->import('routing/**/**.php');
    $services->defaults()->public();
    $services->set('sylius.routing.loader.resource', Resource_Loader::class)->private()->args([service('sylius.resource_registry'), inline_service(Route_Factory::class), '%kernel.environment%', '%sylius.routing_path_bc_layer%'])->tag('routing.loader')->deprecate('sylius/resource', '1.13', 'The "%service_id%" service is deprecated since sylius/resource-bundle 1.13 and will be removed in sylius/resource-bundle 2.0. Use "sylius.symfony.routing.loader.resource" instead.');
    $services->alias(Resource_Loader::class, 'sylius.routing.loader.resource')->private();
    $services->set('sylius.routing.loader.crud_routes_attributes', Crud_Routes_Attributes_Loader::class)->private()->args(['%sylius.resource.mapping%', service('sylius.routing.loader.resource')])->tag('routing.route_loader');
    $services->alias(Crud_Routes_Attributes_Loader::class, 'sylius.routing.loader.crud_routes_attributes')->private();
    $services->set('sylius.routing.loader.routes_attributes', Routes_Attributes_Loader::class)->private()->args(['%sylius.resource.mapping%', service('sylius.routing.factory.route_attributes'), service('sylius.routing.factory.attributes_operation_route')])->tag('routing.route_loader')->deprecate('sylius/resource', '1.13', 'The "%service_id%" service is deprecated since sylius/resource-bundle 1.13 and will be removed in sylius/resource-bundle 2.0. Use "sylius.symfony.routing.loader.resource" instead.');
    $services->alias(Routes_Attributes_Loader::class, 'sylius.routing.loader.routes_attributes')->private();
    $services->set('sylius.routing.factory.operation_route_name_factory', Operation_Route_Name_Factory::class)->private();
    $services->alias(Operation_Route_Name_Factory_Interface::class, 'sylius.routing.factory.operation_route_name_factory');
    $services->set('sylius.routing.factory.operation_route_path_factory.default', Operation_Route_Path_Factory::class)->private();
    $services->alias('sylius.routing.factory.operation_route_path_factory', 'sylius.routing.factory.operation_route_path_factory.default');
    $services->alias(Operation_Route_Path_Factory_Interface::class, 'sylius.routing.factory.operation_route_path_factory');
    $services->set('sylius.routing.factory.operation_route_path_factory.collection', Collection_Operation_Route_Path_Factory::class)->decorate('sylius.routing.factory.operation_route_path_factory.default', null, 60)->args([service('.inner'), service('sylius.path_segment_name_generator')]);
    $services->set('sylius.routing.factory.operation_route_path_factory.create', Create_Operation_Route_Path_Factory::class)->decorate('sylius.routing.factory.operation_route_path_factory.default', null, -50)->args([service('.inner'), service('sylius.path_segment_name_generator')]);
    $services->set('sylius.routing.factory.operation_route_path_factory.bulk_operation', Bulk_Operation_Route_Path_Factory::class)->decorate('sylius.routing.factory.operation_route_path_factory.default', null, -40)->args([service('.inner'), service('sylius.path_segment_name_generator')]);
    $services->set('sylius.routing.factory.operation_route_path_factory.update', Update_Operation_Route_Path_Factory::class)->decorate('sylius.routing.factory.operation_route_path_factory.default', null, -30)->args([service('.inner'), service('sylius.path_segment_name_generator')]);
    $services->set('sylius.routing.factory.operation_route_path_factory.delete', Delete_Operation_Route_Path_Factory::class)->decorate('sylius.routing.factory.operation_route_path_factory.default', null, -20)->args([service('.inner'), service('sylius.path_segment_name_generator')]);
    $services->set('sylius.routing.factory.operation_route_path_factory.show', Show_Operation_Route_Path_Factory::class)->decorate('sylius.routing.factory.operation_route_path_factory.default', null, -10)->args([service('.inner'), service('sylius.path_segment_name_generator')]);
    $services->set('sylius.routing.factory.route_attributes', Route_Attributes_Factory::class)->private();
    $services->alias(Route_Attributes_Factory_Interface::class, 'sylius.routing.factory.route_attributes');
    $services->set('sylius.routing.factory.attributes_operation_route', Attributes_Operation_Route_Factory::class)->private()->args([service('sylius.resource_registry'), service('sylius.routing.factory.operation_route'), service('sylius.resource_metadata_collection.factory')])->deprecate('sylius/resource-bundle', '1.13', 'The "%service_id%" service is deprecated since sylius/resource-bundle 1.13 and will be removed in sylius/resource-bundle 2.0. Use "sylius.routing.resource.route_collection_factory" instead.');
    $services->alias(Attributes_Operation_Route_Factory_Interface::class, 'sylius.routing.factory.attributes_operation_route')->deprecate('sylius/resource-bundle', '1.13', 'The "%alias_id%" service is deprecated since sylius/resource-bundle 1.13 and will be removed in sylius/resource-bundle 2.0. Use "sylius.routing.resource.route_collection_factory" instead.');
    $services->set('sylius.routing.factory.operation_route', Operation_Route_Factory::class)->private()->args([service('sylius.routing.factory.operation_route_path_factory'), service('sylius.path_segment_name_generator'), param('sylius.routing_path_bc_layer')]);
    $services->alias(Operation_Route_Factory_Interface::class, 'sylius.routing.factory.operation_route');
    $services->set('sylius.routing.redirect_handler', Redirect_Handler::class)->args([service('router'), service('sylius.expression_language.argument_parser.routing'), service('sylius.routing.factory.operation_route_name_factory'), service('sylius.grid.filter_storage')->null_on_invalid()]);
    $services->alias(Redirect_Handler_Interface::class, 'sylius.routing.redirect_handler');
};