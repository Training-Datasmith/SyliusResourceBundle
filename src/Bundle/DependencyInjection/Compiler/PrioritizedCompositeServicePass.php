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
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
abstract class Prioritized_Composite_Service_Pass implements Compiler_Pass_Interface
{
    public function __construct(private readonly string $service_id, private readonly string $composite_id, private readonly string $tag_name, private readonly string $method_name)
    {
    }
    public function process(Container_Builder $container): void
    {
        if (!$container->has($this->composite_id)) {
            return;
        }
        $this->inject_tagged_services_into_composite($container);
        $this->add_alias_for_composite_if_service_does_not_exist($container);
    }
    private function inject_tagged_services_into_composite(Container_Builder $container): void
    {
        $context_definition = $container->find_definition($this->composite_id);
        $tagged_services = $container->find_tagged_service_ids($this->tag_name);
        foreach ($tagged_services as $id => $tags) {
            $this->add_method_calls($context_definition, $id, $tags);
        }
    }
    private function add_alias_for_composite_if_service_does_not_exist(Container_Builder $container): void
    {
        if ($container->has($this->service_id)) {
            return;
        }
        $container->set_alias($this->service_id, $this->composite_id)->set_public(true);
    }
    private function add_method_calls(Definition $context_definition, string $id, array $tags): void
    {
        foreach ($tags as $attributes) {
            $this->add_method_call($context_definition, $id, $attributes);
        }
    }
    private function add_method_call(Definition $context_definition, string $id, array $attributes): void
    {
        $context_definition->add_method_call($this->method_name, [new Reference($id), $attributes['priority'] ?? 0]);
    }
}