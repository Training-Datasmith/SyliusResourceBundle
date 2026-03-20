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

use Sylius\Resource\Metadata\Operation\Dash_Path_Segment_Name_Generator;
use Sylius\Resource\Metadata\Operation\Underscore_Path_Segment_Name_Generator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.metadata.path_segment_name_generator.underscore', Underscore_Path_Segment_Name_Generator::class)->args([service('sylius.metadata.inflector')]);
    $services->set('sylius.metadata.path_segment_name_generator.dash', Dash_Path_Segment_Name_Generator::class)->args([service('sylius.metadata.inflector')]);
};