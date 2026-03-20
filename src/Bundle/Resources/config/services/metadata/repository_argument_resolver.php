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

use Sylius\Resource\Symfony\Request\Repository_Argument_Resolver;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.repository_argument_resolver.request', Repository_Argument_Resolver::class);
};