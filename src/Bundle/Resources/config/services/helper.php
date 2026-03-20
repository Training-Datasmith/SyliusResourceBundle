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

use Sylius\Resource\Symfony\Session\Flash\Flash_Helper;
use Sylius\Resource\Symfony\Session\Flash\Flash_Helper_Interface;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.helper.flash', Flash_Helper::class)->args([service('translator')]);
    $services->alias(Flash_Helper_Interface::class, 'sylius.helper.flash');
};