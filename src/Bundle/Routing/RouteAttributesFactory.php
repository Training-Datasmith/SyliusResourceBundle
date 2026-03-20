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
namespace Sylius\Bundle\Resource_Bundle\Routing;

use Sylius\Component\Resource\Annotation\Sylius_Route as LegacySyliusRoute;
use Sylius\Resource\Annotation\Sylius_Route;
use Sylius\Resource\Reflection\Class_Reflection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Route_Collection;
use Webmozart\Assert\Assert;
final class Route_Attributes_Factory implements Route_Attributes_Factory_Interface
{
    public function create_route_for_class(Route_Collection $route_collection, string $class_name): void
    {
        $attributes = Class_Reflection::get_class_attributes($class_name, Sylius_Route::class);
        $attributes = array_merge($attributes, Class_Reflection::get_class_attributes($class_name, Legacy_Sylius_Route::class));
        foreach ($attributes as $reflection_attribute) {
            $arguments = $reflection_attribute->get_arguments();
            Assert::key_exists($arguments, 'name', 'Your route should have a name attribute.');
            $sylius_options = [];
            if (isset($arguments['template'])) {
                $sylius_options['template'] = $arguments['template'];
            }
            if (isset($arguments['vars'])) {
                $sylius_options['vars'] = $arguments['vars'];
            }
            if (isset($arguments['criteria'])) {
                $sylius_options['criteria'] = $arguments['criteria'];
            }
            if (isset($arguments['repository'])) {
                $sylius_options['repository'] = $arguments['repository'];
            }
            if (isset($arguments['serializationGroups'])) {
                $sylius_options['serialization_groups'] = $arguments['serializationGroups'];
            }
            if (isset($arguments['serializationVersion'])) {
                $sylius_options['serialization_version'] = $arguments['serializationVersion'];
            }
            if (isset($arguments['form'])) {
                $sylius_options['form'] = $arguments['form'];
            }
            if (isset($arguments['section'])) {
                $sylius_options['section'] = $arguments['section'];
            }
            if (isset($arguments['permission'])) {
                $sylius_options['permission'] = $arguments['permission'];
            }
            if (isset($arguments['grid'])) {
                $sylius_options['grid'] = $arguments['grid'];
            }
            if (isset($arguments['csrfProtection'])) {
                $sylius_options['csrf_protection'] = $arguments['csrfProtection'];
            }
            if (isset($arguments['redirect'])) {
                $sylius_options['redirect'] = $arguments['redirect'];
            }
            if (isset($arguments['stateMachine'])) {
                $sylius_options['state_machine'] = $arguments['stateMachine'];
            }
            if (isset($arguments['event'])) {
                $sylius_options['event'] = $arguments['event'];
            }
            if (isset($arguments['returnContent'])) {
                $sylius_options['return_content'] = $arguments['returnContent'];
            }
            $route = new Route($arguments['path'], ['_controller' => $arguments['controller'] ?? null, '_sylius' => $sylius_options], $arguments['requirements'] ?? [], $arguments['options'] ?? [], $arguments['host'] ?? '', $arguments['schemes'] ?? [], $arguments['methods'] ?? []);
            $route_collection->add($arguments['name'], $route, $arguments['priority'] ?? 0);
        }
    }
}