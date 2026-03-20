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

use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Exception\InvalidArgumentException;
final class Register_Resources_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        try {
            /** @var array $resources */
            $resources = $container->get_parameter('sylius.resources');
            $registry = $container->find_definition('sylius.resource_registry');
        } catch (InvalidArgumentException) {
            return;
        }
        foreach ($resources as $alias => $configuration) {
            $this->validate_sylius_resource($configuration['classes']['model']);
            $registry->add_method_call('addFromAliasAndConfiguration', [$alias, $configuration]);
        }
    }
    private function validate_sylius_resource(string $class): void
    {
        /** @var array $interfaces */
        $interfaces = class_implements($class);
        if (!in_array(Resource_Interface::class, $interfaces, true)) {
            throw new InvalidArgumentException(sprintf('Class "%s" must implement "%s" to be registered as a Sylius resource.', $class, Resource_Interface::class));
        }
    }
}