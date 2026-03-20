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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection;

use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Extension\Extension;
use Symfony\Component\Dependency_Injection\Extension\Prepend_Extension_Interface;
/**
 * Container extension to bridge the configuration from WhiteOctoberPagerfantaBundle to BabDevPagerfantaBundle
 *
 * @internal
 */
final class Pagerfanta_Extension extends Extension implements Prepend_Extension_Interface
{
    public function __construct(private readonly bool $internal_use = false)
    {
    }
    public function get_alias(): string
    {
        return 'white_october_pagerfanta';
    }
    public function get_configuration(array $config, Container_Builder $container): Pagerfanta_Configuration
    {
        return new Pagerfanta_Configuration();
    }
    public function load(array $configs, Container_Builder $container): void
    {
        if (false === $this->internal_use) {
            trigger_deprecation('sylius/resource-bundle', '1.7', 'The "%s" class is deprecated. Migrate your Pagerfanta configuration from WhiteOctoberPagerfantaBundle to BabDevPagerfantaBundle, the configuration bridge will be removed in 2.0.', self::class);
        }
        $config = $this->process_configuration($this->get_configuration($configs, $container), $configs);
        $container->set_parameter('white_october_pagerfanta.default_view', $config['default_view']);
    }
    public function prepend(Container_Builder $container): void
    {
        $config = $this->process_configuration($this->get_configuration([], $container), $container->get_extension_config($this->get_alias()));
        $container->prepend_extension_config('babdev_pagerfanta', $config);
    }
}