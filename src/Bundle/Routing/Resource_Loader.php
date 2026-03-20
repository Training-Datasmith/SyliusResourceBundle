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

use Behat\Transliterator\Transliterator;
use Gedmo\Sluggable\Util\Urlizer;
use Sylius\Resource\Exception\RuntimeException;
use Sylius\Resource\Metadata\Inflector\Inflector;
use Sylius\Resource\Metadata\Inflector\Inflector_Interface;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Registry_Interface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Route_Collection;
use Symfony\Component\Yaml\Yaml;
/**
 * @deprecated use Sylius\Resource\Symfony\Routing\Loader\ResourceLoader instead
 */
final class Resource_Loader extends Loader
{
    public function __construct(private readonly Registry_Interface $resource_registry, private readonly Route_Factory_Interface $route_factory, ?string $env = null, private readonly ?bool $routing_path_bc_layer = null, private readonly ?Inflector_Interface $inflector = new Inflector())
    {
        parent::__construct($env);
    }
    public function load($resource, $type = null): Route_Collection
    {
        $processor = new Processor();
        $configuration_definition = new Configuration();
        $configuration = Yaml::parse($resource);
        $configuration = $processor->process_configuration($configuration_definition, ['routing' => $configuration]);
        if (!empty($configuration['only']) && !empty($configuration['except'])) {
            throw new \InvalidArgumentException('You can configure only one of "except" & "only" options.');
        }
        $routes_to_generate = ['show', 'index', 'create', 'update', 'delete', 'bulkDelete'];
        if (!empty($configuration['only'])) {
            $routes_to_generate = $configuration['only'];
        }
        if (!empty($configuration['except'])) {
            $routes_to_generate = array_diff($routes_to_generate, $configuration['except']);
        }
        $is_api = $type === 'sylius.resource_api';
        /** @var MetadataInterface $metadata */
        $metadata = $this->resource_registry->get($configuration['alias']);
        $routes = $this->route_factory->create_route_collection();
        $root_path = $configuration['path'] ?? $this->get_root_path($metadata->get_plural_name());
        $identifier = sprintf('{%s}', $configuration['identifier']);
        $bc_layer_enabled = $this->routing_path_bc_layer ?? true;
        $trailing_slash = $bc_layer_enabled ? '/' : '';
        if (in_array('index', $routes_to_generate, true)) {
            $index_route = $this->create_route($metadata, $configuration, $root_path . $trailing_slash, 'index', ['GET'], $is_api);
            $routes->add($this->get_route_name($metadata, $configuration, 'index'), $index_route);
        }
        if (in_array('create', $routes_to_generate, true)) {
            $create_route = $this->create_route($metadata, $configuration, $is_api ? $root_path . $trailing_slash : $root_path . '/new', 'create', $is_api ? ['POST'] : ['GET', 'POST'], $is_api);
            $routes->add($this->get_route_name($metadata, $configuration, 'create'), $create_route);
        }
        if (in_array('update', $routes_to_generate, true)) {
            $http_methods = ['GET', 'PUT', 'PATCH'];
            if (!$bc_layer_enabled) {
                $http_methods[] = 'POST';
            }
            $update_route = $this->create_route($metadata, $configuration, $is_api ? $root_path . '/' . $identifier : $root_path . '/' . $identifier . '/edit', 'update', $is_api ? ['PUT', 'PATCH'] : $http_methods, $is_api);
            $routes->add($this->get_route_name($metadata, $configuration, 'update'), $update_route);
        }
        if (in_array('show', $routes_to_generate, true)) {
            $show_route = $this->create_route($metadata, $configuration, $root_path . '/' . $identifier, 'show', ['GET'], $is_api);
            $routes->add($this->get_route_name($metadata, $configuration, 'show'), $show_route);
        }
        if (!$is_api && in_array('bulkDelete', $routes_to_generate, true)) {
            $http_methods = ['DELETE'];
            if (!$bc_layer_enabled) {
                $http_methods[] = 'POST';
            }
            $bulk_delete_route = $this->create_route($metadata, $configuration, $root_path . '/' . 'bulk-delete', 'bulkDelete', $http_methods, $is_api);
            $routes->add($this->get_route_name($metadata, $configuration, 'bulk_delete'), $bulk_delete_route);
        }
        if (in_array('delete', $routes_to_generate, true)) {
            $http_methods = ['DELETE'];
            if (!$bc_layer_enabled) {
                $http_methods[] = 'POST';
            }
            $delete_route = $this->create_route($metadata, $configuration, $is_api ? $root_path . '/' . $identifier : $root_path . '/' . $identifier . ($bc_layer_enabled ? '' : '/delete'), 'delete', $is_api ? ['DELETE'] : $http_methods, $is_api);
            $routes->add($this->get_route_name($metadata, $configuration, 'delete'), $delete_route);
        }
        return $routes;
    }
    public function supports($resource, $type = null): bool
    {
        return 'sylius.resource' === $type || 'sylius.resource_api' === $type;
    }
    private function get_root_path(string $plural_name): string
    {
        if ($this->routing_path_bc_layer) {
            if (!class_exists(Urlizer::class) || !class_exists(Transliterator::class)) {
                throw new RuntimeException('Cannot use the routing bc-layer when the "behat/transliterator" package is not installed. Try to disable the routing path bc-layer in the Sylius Resource Bundle configuration using "sylius_resource.routing_path_bc_layer: false"');
            }
            return sprintf('/%s', Urlizer::urlize($plural_name));
        }
        return $this->inflector->dashize($plural_name);
    }
    private function create_route(Metadata_Interface $metadata, array $configuration, string $path, string $action_name, array $methods, bool $is_api = false): Route
    {
        $defaults = ['_controller' => $metadata->get_service_id('controller') . sprintf('::%sAction', $action_name)];
        if ($is_api && 'index' === $action_name) {
            $defaults['_sylius']['serialization_groups'] = ['Default'];
        }
        if ($is_api && in_array($action_name, ['show', 'create', 'update'], true)) {
            $defaults['_sylius']['serialization_groups'] = ['Default', 'Detailed'];
        }
        if ($is_api && 'delete' === $action_name) {
            $defaults['_sylius']['csrf_protection'] = false;
        }
        if (isset($configuration['grid']) && 'index' === $action_name) {
            $defaults['_sylius']['grid'] = $configuration['grid'];
        }
        if (isset($configuration['form']) && in_array($action_name, ['create', 'update'], true)) {
            $defaults['_sylius']['form'] = $configuration['form'];
        }
        if (isset($configuration['serialization_version'])) {
            $defaults['_sylius']['serialization_version'] = $configuration['serialization_version'];
        }
        if (isset($configuration['section'])) {
            $defaults['_sylius']['section'] = $configuration['section'];
        }
        if (!empty($configuration['criteria'])) {
            $defaults['_sylius']['criteria'] = $configuration['criteria'];
        }
        if (array_key_exists('filterable', $configuration)) {
            $defaults['_sylius']['filterable'] = $configuration['filterable'];
        }
        if (isset($configuration['templates']) && in_array($action_name, ['show', 'index', 'create', 'update'], true)) {
            $defaults['_sylius']['template'] = sprintf(!str_contains($configuration['templates'], ':') ? '%s/%s.html.twig' : '%s:%s.html.twig', $configuration['templates'], $action_name);
        }
        if (isset($configuration['redirect']) && in_array($action_name, ['create', 'update'], true)) {
            $defaults['_sylius']['redirect'] = $this->get_route_name($metadata, $configuration, $configuration['redirect']);
        }
        if (isset($configuration['permission'])) {
            $defaults['_sylius']['permission'] = $configuration['permission'];
        }
        if (isset($configuration['vars']['all'])) {
            $defaults['_sylius']['vars'] = $configuration['vars']['all'];
        }
        if (isset($configuration['vars'][$action_name])) {
            $vars = $configuration['vars']['all'] ?? [];
            $defaults['_sylius']['vars'] = array_merge($vars, $configuration['vars'][$action_name]);
        }
        if ($action_name === 'bulkDelete') {
            $defaults['_sylius']['paginate'] = false;
            $defaults['_sylius']['repository'] = ['method' => 'findById', 'arguments' => ['$ids']];
        }
        $condition = $configuration['condition'] ?? '';
        return $this->route_factory->create_route($path, $defaults, [], [], '', [], $methods, $condition);
    }
    private function get_route_name(Metadata_Interface $metadata, array $configuration, string $action_name): string
    {
        $section_prefix = isset($configuration['section']) ? $configuration['section'] . '_' : '';
        return sprintf('%s_%s%s_%s', $metadata->get_application_name(), $section_prefix, $metadata->get_name(), $action_name);
    }
}