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

use Sylius\Resource\Metadata\Mutator\Operation_Mutator_Collection;
use Sylius\Resource\Metadata\Mutator\Resource_Mutator_Collection;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.metadata.mutator_collection.resource', Resource_Mutator_Collection::class)->private();
    $services->set('sylius.metadata.mutator_collection.operation', Operation_Mutator_Collection::class)->private();
};