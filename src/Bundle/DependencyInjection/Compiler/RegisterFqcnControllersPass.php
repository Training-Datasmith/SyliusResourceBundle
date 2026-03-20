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

use Sylius\Bundle\Resource_Bundle\Controller\Resource_Controller;
use Sylius\Resource\Metadata\Metadata;
use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Exception\InvalidArgumentException;
/** @internal */
final class Register_Fqcn_Controllers_Pass implements Compiler_Pass_Interface
{
    public function process(Container_Builder $container): void
    {
        try {
            /** @var array $resources */
            $resources = $container->get_parameter('sylius.resources');
        } catch (InvalidArgumentException) {
            return;
        }
        foreach ($resources as $alias => $configuration) {
            $metadata = Metadata::from_alias_and_configuration($alias, $configuration);
            if (!$metadata->has_class('controller')) {
                continue;
            }
            $this->validate_sylius_resource($metadata->get_class('model'));
            $controller_fqcn = $metadata->get_class('controller');
            if ($controller_fqcn !== Resource_Controller::class) {
                $definition = $container->get_definition($metadata->get_service_id('controller'));
                // TODO: Change to alias definition after bumping to > Symfony 5.1
                $container->set_definition($metadata->get_class('controller'), $definition);
            }
        }
    }
    private function validate_sylius_resource(string $class): void
    {
        if (!in_array(Resource_Interface::class, class_implements($class) ?: [], true)) {
            throw new InvalidArgumentException(sprintf('Class "%s" must implement "%s" to be registered as a Sylius resource.', $class, Resource_Interface::class));
        }
    }
}