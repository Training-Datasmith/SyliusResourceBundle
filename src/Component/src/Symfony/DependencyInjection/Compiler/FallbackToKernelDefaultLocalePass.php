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
namespace Sylius\Resource\Symfony\Dependency_Injection\Compiler;

use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
/**
 * @internal
 */
final class Fallback_To_Kernel_Default_Locale_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        if ($container->has_parameter('locale') || !$container->has_parameter('kernel.default_locale')) {
            return;
        }
        /** @var string $locale */
        $locale = $container->get_parameter('kernel.default_locale');
        $this->replace_locale_in_flash_helper($container, $locale);
        $this->replace_locale_provider($container, $locale);
    }
    private function replace_locale_in_flash_helper(Container_Builder $container, string $locale): void
    {
        if (!$container->has_definition('sylius.resource_controller.flash_helper')) {
            return;
        }
        $flash_helper = $container->get_definition('sylius.resource_controller.flash_helper');
        $flash_helper->replace_argument(2, $locale);
    }
    private function replace_locale_provider(Container_Builder $container, string $locale): void
    {
        if (!$container->has_definition('sylius.translation_locale_provider.immutable')) {
            return;
        }
        $locale_provider = $container->get_definition('sylius.translation_locale_provider.immutable');
        $locale_provider->replace_argument(0, [$locale]);
        $locale_provider->replace_argument(1, $locale);
    }
}