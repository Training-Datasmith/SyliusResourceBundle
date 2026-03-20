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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Helper;

use Sylius\Resource\Model\Resource_Interface;
final class Target_Entities_Resolver implements Target_Entities_Resolver_Interface
{
    public function resolve(array $resources_configuration): array
    {
        $interfaces = [];
        foreach ($resources_configuration as $alias => $configuration) {
            $model = $this->get_model($alias, $configuration);
            $model_interfaces = class_implements($model) ?: [];
            foreach ($model_interfaces as $interface) {
                if ($interface === Resource_Interface::class) {
                    continue;
                }
                if (isset($interfaces[$interface]) && in_array($model, $interfaces[$interface], true)) {
                    continue;
                }
                $interfaces[$interface][] = $model;
            }
        }
        $interfaces = array_filter($interfaces, static fn(array $classes): bool => count($classes) === 1);
        $interfaces = array_map(static fn(array $classes): string => current($classes), $interfaces);
        foreach ($resources_configuration as $alias => $configuration) {
            if (isset($configuration['classes']['interface'])) {
                $model = $this->get_model($alias, $configuration);
                $interface = $configuration['classes']['interface'];
                trigger_deprecation('sylius/resource-bundle', '1.6', 'Specifying the interface for resources is deprecated and will be removed in 2.0. Please rely on auto-discovering interfaces instead. Triggered by resource "%s" with model "%s" and interface "%s".', $alias, $model, $interface);
                $interfaces[$interface] = $model;
            }
        }
        return $interfaces;
    }
    private function get_model(string $alias, array $configuration): string
    {
        if (!isset($configuration['classes']['model'])) {
            throw new \InvalidArgumentException(sprintf('Could not get model class from resource "%s".', $alias));
        }
        return $configuration['classes']['model'];
    }
}