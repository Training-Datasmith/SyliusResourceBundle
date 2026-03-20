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

use Hateoas\Representation\Factory\Pagerfanta_Factory;
use Sylius\Bundle\Resource_Bundle\Controller\Disabled_Authorization_Checker;
use Sylius\Bundle\Resource_Bundle\Controller\Event_Dispatcher;
use Sylius\Bundle\Resource_Bundle\Controller\Event_Dispatcher_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Flash_Helper;
use Sylius\Bundle\Resource_Bundle\Controller\Flash_Helper_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\New_Resource_Factory;
use Sylius\Bundle\Resource_Bundle\Controller\New_Resource_Factory_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Parameters_Parser;
use Sylius\Bundle\Resource_Bundle\Controller\Parameters_Parser_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Redirect_Handler;
use Sylius\Bundle\Resource_Bundle\Controller\Redirect_Handler_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration;
use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration_Factory;
use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration_Factory_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Delete_Handler;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Delete_Handler_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Form_Factory;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Form_Factory_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Resources_Collection_Provider;
use Sylius\Bundle\Resource_Bundle\Controller\Resources_Collection_Provider_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Resources_Resolver;
use Sylius\Bundle\Resource_Bundle\Controller\Resources_Resolver_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Update_Handler;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Update_Handler_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Single_Resource_Provider;
use Sylius\Bundle\Resource_Bundle\Controller\Single_Resource_Provider_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\View_Handler;
use Sylius\Bundle\Resource_Bundle\Controller\View_Handler_Interface;
use Sylius\Resource\Symfony\Controller\Main_Controller;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.main_controller', Main_Controller::class)->args([service('sylius.resource_metadata_operation.initiator.http_operation'), service('sylius.context.initiator.request_context'), service('sylius.state_provider.main'), service('sylius.state_processor.main')])->tag('controller.service_arguments');
    $services->set('sylius.resource_controller.parameters_parser', Parameters_Parser::class)->args([service('service_container'), service('sylius.expression_language')]);
    $services->alias(Parameters_Parser_Interface::class, 'sylius.resource_controller.parameters_parser');
    $services->set('sylius.resource_controller.request_configuration_factory', Request_Configuration_Factory::class)->args([service('sylius.resource_controller.parameters_parser'), Request_Configuration::class, '%sylius.resource.settings%']);
    $services->alias(Request_Configuration_Factory_Interface::class, 'sylius.resource_controller.request_configuration_factory');
    $services->set('sylius.resource_controller.new_resource_factory', New_Resource_Factory::class);
    $services->alias(New_Resource_Factory_Interface::class, 'sylius.resource_controller.new_resource_factory');
    $services->set('sylius.resource_controller.single_resource_provider', Single_Resource_Provider::class);
    $services->alias(Single_Resource_Provider_Interface::class, 'sylius.resource_controller.single_resource_provider');
    $services->set('sylius.resource_controller.pagerfanta_representation_factory', Pagerfanta_Factory::class);
    $services->alias(Pagerfanta_Factory::class, 'sylius.resource_controller.pagerfanta_representation_factory');
    $services->set('sylius.resource_controller.resources_resolver', Resources_Resolver::class);
    $services->alias(Resources_Resolver_Interface::class, 'sylius.resource_controller.resources_resolver');
    $services->set('sylius.resource_controller.resources_collection_provider', Resources_Collection_Provider::class)->args([service('sylius.resource_controller.resources_resolver'), service('sylius.resource_controller.pagerfanta_representation_factory')->null_on_invalid()]);
    $services->alias(Resources_Collection_Provider_Interface::class, 'sylius.resource_controller.resources_collection_provider');
    $services->set('sylius.resource_controller.form_factory', Resource_Form_Factory::class)->args([service('form.factory')]);
    $services->alias(Resource_Form_Factory_Interface::class, 'sylius.resource_controller.form_factory');
    $services->set('sylius.resource_controller.redirect_handler', Redirect_Handler::class)->args([service('router')]);
    $services->alias(Redirect_Handler_Interface::class, 'sylius.resource_controller.redirect_handler');
    $services->set('sylius.resource_controller.authorization_checker.disabled', Disabled_Authorization_Checker::class);
    $services->alias(Disabled_Authorization_Checker::class, 'sylius.resource_controller.authorization_checker.disabled');
    $services->set('sylius.resource_controller.flash_helper', Flash_Helper::class)->args([service('request_stack'), service('translator'), '%locale%']);
    $services->alias(Flash_Helper_Interface::class, 'sylius.resource_controller.flash_helper');
    $services->set('sylius.resource_controller.event_dispatcher', Event_Dispatcher::class)->args([service('event_dispatcher')]);
    $services->alias(Event_Dispatcher_Interface::class, 'sylius.resource_controller.event_dispatcher');
    $services->set('sylius.resource_controller.view_handler', View_Handler::class)->args([service('fos_rest.view_handler')->null_on_invalid()]);
    $services->alias(View_Handler_Interface::class, 'sylius.resource_controller.view_handler');
    $services->set('sylius.resource_controller.resource_update_handler', Resource_Update_Handler::class)->args([service('sylius.resource_controller.state_machine')->null_on_invalid()]);
    $services->alias(Resource_Update_Handler_Interface::class, 'sylius.resource_controller.resource_update_handler');
    $services->set('sylius.resource_controller.resource_delete_handler', Resource_Delete_Handler::class);
    $services->alias(Resource_Delete_Handler_Interface::class, 'sylius.resource_controller.resource_delete_handler');
};