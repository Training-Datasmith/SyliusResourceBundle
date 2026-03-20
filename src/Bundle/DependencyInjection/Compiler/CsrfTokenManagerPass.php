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
/**
 * TODO Remove on sylius/resource-bundle 2.0
 */
final class Csrf_Token_Manager_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition('security.csrf.token_manager')) {
            return;
        }
        $csrd_token_manager_definition = $container->get_definition('security.csrf.token_manager');
        $csrd_token_manager_definition->set_public(true);
    }
}