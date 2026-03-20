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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Extension;

use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver\Driver_Provider;
use Sylius\Resource\Metadata\Metadata;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Extension\Extension;
abstract class Abstract_Resource_Extension extends Extension
{
    protected function register_resources(string $application_name, string $driver, array $registered_resources, Container_Builder $container): void
    {
        $container->set_parameter(sprintf('%s.driver.%s', $this->get_alias(), $driver), true);
        $container->set_parameter(sprintf('%s.driver', $this->get_alias()), $driver);
        /** @var array<string, array> $resources */
        $resources = $container->has_parameter('sylius.resources') ? $container->get_parameter('sylius.resources') : [];
        foreach ($registered_resources as $resource_name => $resource_config) {
            $alias = $application_name . '.' . $resource_name;
            $resource_config = array_merge(['driver' => $driver], $resource_config);
            $resources[$alias] = $resource_config;
            $container->set_parameter('sylius.resources', $resources);
            $metadata = Metadata::from_alias_and_configuration($alias, $resource_config);
            Driver_Provider::get($metadata)->load($container, $metadata);
            if ($metadata->has_parameter('translation')) {
                $alias .= '_translation';
                $resource_config = array_merge(['driver' => $driver], $resource_config['translation']);
                $resources[$alias] = $resource_config;
                $container->set_parameter('sylius.resources', $resources);
                $metadata = Metadata::from_alias_and_configuration($alias, $resource_config);
                Driver_Provider::get($metadata)->load($container, $metadata);
            }
        }
    }
}