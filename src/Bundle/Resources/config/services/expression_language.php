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

use Sylius\Resource\Symfony\Expression_Language\Argument_Parser;
use Sylius\Resource\Symfony\Expression_Language\Provider\Throw_Not_Found_On_Null_Expression_Function_Provider;
use Sylius\Resource\Symfony\Expression_Language\Request_Variables;
use Sylius\Resource\Symfony\Expression_Language\Sylius_Repositories_Variables;
use Sylius\Resource\Symfony\Expression_Language\Token_Variables;
use Sylius\Resource\Symfony\Expression_Language\Variables_Collection;
use Sylius\Resource\Symfony\Expression_Language\Vars_Resolver;
use Symfony\Component\Expression_Language\Expression_Language;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.metadata.expression_language', Expression_Language::class);
    $services->set('sylius.resource_factory.expression_language', Expression_Language::class);
    $services->set('sylius.repository.expression_language', Expression_Language::class);
    $services->set('sylius.routing.expression_language', Expression_Language::class);
    $services->set('sylius.expression_language.variables.token', Token_Variables::class)->args([service('security.token_storage')->null_on_invalid()])->tag('sylius.metadata_variables')->tag('sylius.resource_factory_variables')->tag('sylius.repository_variables');
    $services->set('sylius.expression_language.variables.request', Request_Variables::class)->args([service('request_stack')])->tag('sylius.metadata_variables')->tag('sylius.resource_factory_variables')->tag('sylius.repository_variables')->tag('sylius.routing_variables');
    $services->set('sylius.expression_language.variables.sylius_repositories', Sylius_Repositories_Variables::class)->args([tagged_locator('sylius.repository')])->tag('sylius.metadata_variables')->tag('sylius.resource_factory_variables');
    $services->set('sylius.expression_language.variables_collection.metadata', Variables_Collection::class)->args([tagged_iterator('sylius.metadata_variables')]);
    $services->set('sylius.expression_language.variables_collection.factory', Variables_Collection::class)->args([tagged_iterator('sylius.resource_factory_variables')]);
    $services->set('sylius.expression_language.variables_collection.form', Variables_Collection::class)->args([tagged_iterator('sylius.form_variables')]);
    $services->set('sylius.expression_language.variables_collection.repository', Variables_Collection::class)->args([tagged_iterator('sylius.repository_variables')]);
    $services->set('sylius.expression_language.variables_collection.routing', Variables_Collection::class)->args([tagged_iterator('sylius.routing_variables')]);
    $services->set('sylius.expression_language.providers.throw_not_found_on_null', Throw_Not_Found_On_Null_Expression_Function_Provider::class)->tag('sylius.metadata_providers')->tag('sylius.resource_factory_providers');
    $services->set('sylius.expression_language.vars_resolver.metadata', Vars_Resolver::class)->args([service('sylius.expression_language.argument_parser.metadata')]);
    $services->set('sylius.expression_language.argument_parser.metadata', Argument_Parser::class)->args([service('sylius.metadata.expression_language'), service('sylius.expression_language.variables_collection.metadata'), tagged_iterator('sylius.metadata_providers')]);
    $services->set('sylius.expression_language.argument_parser.factory', Argument_Parser::class)->args([service('sylius.resource_factory.expression_language'), service('sylius.expression_language.variables_collection.factory'), tagged_iterator('sylius.resource_factory_providers')]);
    $services->set('sylius.expression_language.argument_parser.form', Argument_Parser::class)->args([service('sylius.resource_factory.expression_language'), service('sylius.expression_language.variables_collection.form'), tagged_iterator('sylius.resource_factory_providers')]);
    $services->set('sylius.expression_language.argument_parser.repository', Argument_Parser::class)->args([service('sylius.repository.expression_language'), service('sylius.expression_language.variables_collection.repository'), tagged_iterator('sylius.repository_providers')]);
    $services->set('sylius.expression_language.argument_parser.routing', Argument_Parser::class)->args([service('sylius.routing.expression_language'), service('sylius.expression_language.variables_collection.routing'), tagged_iterator('sylius.routing_providers')]);
};