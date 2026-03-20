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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection;

use Sylius\Bundle\Resource_Bundle\Controller\Resource_Controller;
use Sylius\Bundle\Resource_Bundle\Form\Type\Default_Resource_Type;
use Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle;
use Sylius\Resource\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Configuration_Interface;
final class Configuration implements Configuration_Interface
{
    /**
     * @return TreeBuilder<'array'>
     */
    public function get_config_tree_builder(): Tree_Builder
    {
        $tree_builder = new Tree_Builder('sylius_resource');
        /** @var ArrayNodeDefinition<TreeBuilder<'array'>> $rootNode */
        $root_node = $tree_builder->get_root_node();
        $this->add_resources_section($root_node);
        $this->add_settings_section($root_node);
        $this->add_translations_section($root_node);
        $this->add_drivers_section($root_node);
        $root_node->children()->array_node('mapping')->add_defaults_if_not_set()->children()->array_node('imports')->prototype('scalar')->end()->end()->array_node('paths')->prototype('scalar')->end()->end()->end()->end()->scalar_node('authorization_checker')->default_value('sylius.resource_controller.authorization_checker.disabled')->cannot_be_empty()->end()->boolean_node('routing_path_bc_layer')->end()->scalar_node('path_segment_name_generator')->default_value('sylius.metadata.path_segment_name_generator.dash')->info('Specify a path name generator to use.')->end()->end();
        return $tree_builder;
    }
    /**
     * @param ArrayNodeDefinition<TreeBuilder<'array'>> $node
     */
    private function add_resources_section(Array_Node_Definition $node): void
    {
        $node->children()->array_node('resources')->use_attribute_as_key('name')->array_prototype()->children()->scalar_node('driver')->default_value(Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM)->end()->variable_node('options')->set_deprecated('sylius/resource-bundle', '1.12', 'The "%node%" node at "%path%" is deprecated and will be removed in 2.0.')->end()->scalar_node('templates')->cannot_be_empty()->end()->scalar_node('state_machine_component')->default_null()->end()->array_node('classes')->is_required()->add_defaults_if_not_set()->children()->scalar_node('model')->is_required()->cannot_be_empty()->end()->scalar_node('interface')->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->scalar_node('form')->default_value(Default_Resource_Type::class)->cannot_be_empty()->end()->end()->end()->array_node('translation')->children()->variable_node('options')->set_deprecated('sylius/resource-bundle', '1.12', 'The "%node%" node at "%path%" is deprecated and will be removed in 2.0.')->end()->array_node('classes')->is_required()->add_defaults_if_not_set()->children()->scalar_node('model')->is_required()->cannot_be_empty()->end()->scalar_node('interface')->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->scalar_node('form')->default_value(Default_Resource_Type::class)->cannot_be_empty()->end()->end()->end()->end()->end()->end()->end()->end()->end();
    }
    /**
     * @param ArrayNodeDefinition<TreeBuilder<'array'>> $node
     */
    private function add_settings_section(Array_Node_Definition $node): void
    {
        $node->children()->array_node('settings')->add_defaults_if_not_set()->children()->variable_node('paginate')->default_null()->end()->variable_node('limit')->default_null()->end()->array_node('allowed_paginate')->integer_prototype()->end()->default_value([10, 20, 30])->end()->integer_node('default_page_size')->default_value(10)->end()->scalar_node('default_templates_dir')->default_null()->end()->boolean_node('sortable')->default_false()->end()->variable_node('sorting')->default_null()->end()->boolean_node('filterable')->default_false()->end()->variable_node('criteria')->default_null()->end()->scalar_node('state_machine_component')->default_null()->end()->end()->end()->end();
    }
    /**
     * @param ArrayNodeDefinition<TreeBuilder<'array'>> $node
     */
    private function add_translations_section(Array_Node_Definition $node): void
    {
        $node->children()->array_node('translation')->can_be_disabled()->children()->scalar_node('locale_provider')->default_value('sylius.translation_locale_provider.immutable')->cannot_be_empty()->end()->end()->end();
    }
    /**
     * @param ArrayNodeDefinition<TreeBuilder<'array'>> $node
     */
    private function add_drivers_section(Array_Node_Definition $node): void
    {
        $node->children()->array_node('drivers')->default_value([])->enum_prototype()->values(Sylius_Resource_Bundle::get_available_drivers())->end()->end()->end();
    }
}