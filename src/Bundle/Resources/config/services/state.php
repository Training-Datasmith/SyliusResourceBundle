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

use Sylius\Resource\Doctrine\Common\State\Persist_Processor;
use Sylius\Resource\State\Factory;
use Sylius\Resource\State\Factory_Interface;
use Sylius\Resource\State\Provider;
use Sylius\Resource\State\Provider_Interface;
use Sylius\Resource\State\Responder;
use Sylius\Resource\State\Responder_Interface;
use Sylius\Resource\Symfony\Request\State\Api_Responder;
use Sylius\Resource\Symfony\Request\State\Twig_Responder;
use Sylius\Resource\Symfony\Response\Api_Headers_Initiator;
use Symfony\Component\Expression_Language\Expression_Language;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $container->import('state/**/*.php');
    $services->set('sylius.resource_factory.expression_language', Expression_Language::class);
    $services->set('sylius.state_provider.locator', Provider::class)->args([tagged_locator('sylius.state_provider')]);
    $services->alias(Provider_Interface::class, 'sylius.state_provider');
    $services->set('sylius.state_factory', Factory::class)->args([tagged_locator('sylius.resource_factory'), service('sylius.expression_language.argument_parser.factory')]);
    $services->alias(Factory_Interface::class, 'sylius.state_factory');
    $services->set('sylius.state_responder', Responder::class)->args([tagged_locator('sylius.state_responder')]);
    $services->alias(Responder_Interface::class, 'sylius.state_responder');
    $services->set(\Sylius\Resource\Symfony\Request\State\Provider::class)->args([tagged_locator('sylius.repository'), service('sylius.repository_argument_resolver.request'), service('sylius.expression_language.argument_parser.repository')])->tag('sylius.state_provider');
    $services->set(\Sylius\Resource\State_Machine\State\Apply_State_Machine_Transition_Processor::class)->args([service('sylius.state_machine.operation'), service(Persist_Processor::class)->null_on_invalid()])->tag('sylius.state_processor');
    $services->set(\Sylius\Resource\Symfony\Request\State\Responder::class)->args([tagged_locator('sylius.state_responder')])->tag('sylius.state_responder');
    $services->set('sylius.state_responder.html', Twig_Responder::class)->args([service('sylius.routing.redirect_handler'), service('sylius.twig.context.factory'), service('twig')->null_on_invalid()])->tag('sylius.state_responder');
    $services->set('sylius.headers_initiator.api', Api_Headers_Initiator::class);
    $services->set('sylius.state_responder.api', Api_Responder::class)->args([service('sylius.headers_initiator.api')])->tag('sylius.state_responder');
    $services->set(\Sylius\Resource\Grid\State\Request_Grid_Provider::class)->args([service('sylius.grid.view_factory.resource')->null_on_invalid(), service('sylius.grid.provider')->null_on_invalid()])->tag('sylius.state_provider');
};