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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler;

use Bazinga\Bundle\Hateoas_Bundle\Bazinga_Hateoas_Bundle;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
final class Unregister_Hateoas_Definitions_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        /** @var array $bundles */
        $bundles = $container->get_parameter('kernel.bundles');
        if (class_exists(Bazinga_Hateoas_Bundle::class) && in_array(Bazinga_Hateoas_Bundle::class, $bundles, true)) {
            return;
        }
        $container->remove_definition('sylius.resource_controller.pagerfanta_representation_factory');
    }
}