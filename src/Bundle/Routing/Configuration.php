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
        $tree_builder = new Tree_Builder('routing');
        /** @var ArrayNodeDefinition<TreeBuilder<'array'>> $rootNode */
        $root_node = $tree_builder->get_root_node();
        $root_node->children()->scalar_node('alias')->is_required()->cannot_be_empty()->end()->scalar_node('path')->default_value(null)->end()->scalar_node('identifier')->default_value('id')->end()->array_node('criteria')->use_attribute_as_key('identifier')->scalar_prototype()->end()->end()->boolean_node('filterable')->end()->variable_node('form')->cannot_be_empty()->end()->scalar_node('serialization_version')->cannot_be_empty()->end()->scalar_node('section')->cannot_be_empty()->end()->scalar_node('redirect')->cannot_be_empty()->end()->scalar_node('templates')->cannot_be_empty()->end()->scalar_node('grid')->cannot_be_empty()->end()->boolean_node('permission')->default_value(false)->end()->scalar_node('condition')->cannot_be_empty()->end()->array_node('except')->scalar_prototype()->end()->end()->array_node('only')->scalar_prototype()->end()->end()->variable_node('vars')->cannot_be_empty()->end()->end();
        return $tree_builder;
    }
}