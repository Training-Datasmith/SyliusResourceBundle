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

use Sylius\Resource\Metadata\Operation_Access_Checker_Interface;
use Sylius\Resource\Symfony\Security\Operation_Access_Checker;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.security.operation_access_checker', Operation_Access_Checker::class)->args([service('sylius.expression_language')->null_on_invalid(), service('security.authentication.trust_resolver')->null_on_invalid(), service('security.role_hierarchy')->null_on_invalid(), service('security.token_storage')->null_on_invalid(), service('security.authorization_checker')->null_on_invalid()]);
    $services->alias(Operation_Access_Checker_Interface::class, 'sylius.security.operation_access_checker');
};