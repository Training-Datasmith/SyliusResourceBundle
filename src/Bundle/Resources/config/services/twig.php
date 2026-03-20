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

use Sylius\Bundle\Resource_Bundle\Twig\Context\Legacy_Context_Factory;
use Sylius\Resource\Twig\Context\Factory\Context_Factory;
use Sylius\Resource\Twig\Context\Factory\Context_Factory_Interface;
use Sylius\Resource\Twig\Context\Factory\Default_Context_Factory;
use Sylius\Resource\Twig\Context\Factory\Request_Context_Factory;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.twig.context.factory', Context_Factory::class)->args([tagged_locator('sylius.twig_context_factory')]);
    $services->set('sylius.twig.context.factory.default', Default_Context_Factory::class)->tag('sylius.twig_context_factory');
    $services->alias(Context_Factory_Interface::class, 'sylius.twig.context.factory.default');
    $services->set('sylius.twig.context.factory.request', Request_Context_Factory::class)->decorate('sylius.twig.context.factory')->args([service('.inner')]);
    $services->set('sylius.twig.context.factory.legacy', Legacy_Context_Factory::class)->decorate('sylius.twig.context.factory')->args([service('.inner')]);
};