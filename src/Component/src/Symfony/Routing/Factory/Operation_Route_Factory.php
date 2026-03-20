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
namespace Sylius\Resource\Symfony\Routing\Factory;

use Behat\Transliterator\Transliterator;
use Gedmo\Sluggable\Util\Urlizer;
use Sylius\Resource\Exception\RuntimeException;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Operation\Path_Segment_Name_Generator_Interface;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Symfony\Routing\Factory\Route_Path\Operation_Route_Path_Factory_Interface;
use Symfony\Component\Routing\Route;
/**
 * @experimental
 */
final readonly class Operation_Route_Factory implements Operation_Route_Factory_Interface
{
    public function __construct(private Operation_Route_Path_Factory_Interface $route_path_factory, private Path_Segment_Name_Generator_Interface $path_segment_name_generator, private bool $routing_path_bc_layer = true)
    {
    }
    public function create(Metadata_Interface $metadata, Resource_Metadata $resource, Http_Operation $operation): Route
    {
        $route_path = $operation->get_path() ?? $this->get_default_route_path($metadata, $resource, $operation);
        if (null !== $route_prefix = $operation->get_route_prefix()) {
            $route_path = sprintf('%s/%s', rtrim($route_prefix, '/'), ltrim($route_path, '/'));
        }
        return new Route(path: $route_path, defaults: ['_controller' => 'sylius.main_controller', '_sylius' => $this->get_sylius_options($resource, $operation)], requirements: $operation->get_route_requirements() ?? [], methods: $operation->get_methods() ?? [], condition: $operation->get_route_condition());
    }
    private function get_default_route_path(Metadata_Interface $legacy_metadata, Resource_Metadata $resource, Http_Operation $operation): string
    {
        return $this->get_default_route_path_for_operation($legacy_metadata, $resource, $operation);
    }
    private function get_default_route_path_for_operation(Metadata_Interface $legacy_metadata, Resource_Metadata $resource, Http_Operation $operation): string
    {
        if (null !== $path = $operation->get_path()) {
            return $path;
        }
        return $this->route_path_factory->create_route_path($operation, $this->get_root_path($legacy_metadata, $resource));
    }
    private function get_root_path(Metadata_Interface $legacy_metadata, Resource_Metadata $resource): string
    {
        if ($this->routing_path_bc_layer) {
            if (!class_exists(Urlizer::class) || !class_exists(Transliterator::class)) {
                throw new RuntimeException('Cannot use the routing bc-layer when the "behat/transliterator" package is not installed. Try to disable the routing path bc-layer in the Sylius Resource Bundle configuration using "sylius_resource.routing_path_bc_layer: false"');
            }
            return Urlizer::urlize($legacy_metadata->get_plural_name());
        }
        return $this->path_segment_name_generator->get_segment_name(name: $resource->get_plural_name() ?? '', pluralize: false);
    }
    private function get_sylius_options(Resource_Metadata $resource, Http_Operation $operation): array
    {
        $options = ['resource' => $resource->get_alias()];
        if (null !== $section = $resource->get_section()) {
            $options['section'] = $section;
        }
        // For Legacy Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration
        if (null !== $vars = $operation->get_vars()) {
            $options['vars'] = $vars;
        }
        return $options;
    }
}