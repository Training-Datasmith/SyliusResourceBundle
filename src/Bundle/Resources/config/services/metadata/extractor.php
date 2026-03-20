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

use Sylius\Resource\Metadata\Extractor\Php_File_Resource_Extractor;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.metadata.resource_extractor.php_file', Php_File_Resource_Extractor::class)->args([[], service('service_container')]);
};