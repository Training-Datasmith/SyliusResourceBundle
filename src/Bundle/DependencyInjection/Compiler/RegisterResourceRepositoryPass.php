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

use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Reference;
final class Register_Resource_Repository_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        if (!$container->has_parameter('sylius.resources') || !$container->has('sylius.registry.resource_repository')) {
            return;
        }
        /** @var array $resources */
        $resources = $container->get_parameter('sylius.resources');
        $repository_registry = $container->find_definition('sylius.registry.resource_repository');
        foreach ($resources as $alias => $configuration) {
            [$application_name, $resource_name] = explode('.', (string) $alias, 2);
            $repository_id = sprintf('%s.repository.%s', $application_name, $resource_name);
            if ($container->has($repository_id)) {
                $repository_registry->add_method_call('register', [$alias, new Reference($repository_id)]);
            }
        }
    }
}