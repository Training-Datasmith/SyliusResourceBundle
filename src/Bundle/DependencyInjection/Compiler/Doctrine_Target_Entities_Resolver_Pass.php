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

use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Helper\Target_Entities_Resolver_Interface;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Exception\InvalidArgumentException;
/**
 * Resolves given target entities with container parameters.
 * Usable only with *doctrine/orm* driver.
 */
final readonly class Doctrine_Target_Entities_Resolver_Pass implements Compiler_Pass_Interface
{
    public function __construct(private Target_Entities_Resolver_Interface $target_entities_resolver)
    {
    }
    public function process(Container_Builder $container): void
    {
        try {
            /** @var array $resources */
            $resources = $container->get_parameter('sylius.resources');
            $resolve_target_entity_listener = $container->find_definition('doctrine.orm.listeners.resolve_target_entity');
        } catch (InvalidArgumentException) {
            return;
        }
        $interfaces = $this->target_entities_resolver->resolve($resources);
        foreach ($interfaces as $interface => $model) {
            $resolve_target_entity_listener->add_method_call('addResolveTargetEntity', [$interface, $model, []]);
        }
        if (!$resolve_target_entity_listener->has_tag('doctrine.event_listener')) {
            $resolve_target_entity_listener->add_tag('doctrine.event_listener', ['event' => 'loadClassMetadata']);
            $resolve_target_entity_listener->add_tag('doctrine.event_listener', ['event' => 'onClassMetadataNotFound']);
        }
    }
}