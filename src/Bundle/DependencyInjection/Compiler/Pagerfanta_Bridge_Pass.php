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

use Symfony\Component\Dependency_Injection\Alias;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
/**
 * Compiler pass to bridge the configuration from WhiteOctoberPagerfantaBundle to BabDevPagerfantaBundle
 *
 * @internal
 */
final readonly class Pagerfanta_Bridge_Pass implements Compiler_Pass_Interface
{
    public function __construct(private bool $internal_use = false)
    {
    }
    public function process(Container_Builder $container): void
    {
        if (false === $this->internal_use) {
            trigger_deprecation('sylius/resource-bundle', '1.7', 'The "%s" class is deprecated. Migrate your Pagerfanta configuration from WhiteOctoberPagerfantaBundle to BabDevPagerfantaBundle, the configuration bridge will be removed in 2.0.', self::class);
        }
        $this->change_view_factory_class($container);
        $this->alias_renamed_services($container);
    }
    private function change_view_factory_class(Container_Builder $container): void
    {
        if (!$container->has_parameter('white_october_pagerfanta.view_factory.class') || !$container->has_definition('pagerfanta.view_factory')) {
            return;
        }
        /** @var string $viewFactoryClass */
        $view_factory_class = $container->get_parameter('white_october_pagerfanta.view_factory.class');
        $container->get_definition('pagerfanta.view_factory')->set_class($view_factory_class);
    }
    private function alias_renamed_services(Container_Builder $container): void
    {
        $set_deprecated_method = (new \ReflectionClass(Alias::class))->get_method('setDeprecated');
        if ($container->has_definition('pagerfanta.twig_extension')) {
            if (2 === $set_deprecated_method->get_number_of_parameters()) {
                $container->set_alias('twig.extension.pagerfanta', 'pagerfanta.twig_extension')->set_deprecated(true, 'The "%alias_id%" service alias is deprecated since Sylius 1.8, use the "pagerfanta.twig_extension" service ID instead.');
            } else {
                $container->set_alias('twig.extension.pagerfanta', 'pagerfanta.twig_extension')->set_deprecated('sylius/resource-bundle', '1.8', 'The "%alias_id%" service alias is deprecated since Sylius 1.8, use the "pagerfanta.twig_extension" service ID instead.');
            }
        }
        if ($container->has_definition('pagerfanta.view_factory')) {
            if (2 === $set_deprecated_method->get_number_of_parameters()) {
                $container->set_alias('white_october_pagerfanta.view_factory', 'pagerfanta.view_factory')->set_deprecated(true, 'The "%alias_id%" service alias is deprecated since Sylius 1.8, use the "pagerfanta.view_factory" service ID instead.');
            } else {
                $container->set_alias('white_october_pagerfanta.view_factory', 'pagerfanta.view_factory')->set_deprecated('sylius/resource-bundle', '1.8', 'The "%alias_id%" service alias is deprecated since Sylius 1.8, use the "pagerfanta.view_factory" service ID instead.');
            }
        }
    }
}