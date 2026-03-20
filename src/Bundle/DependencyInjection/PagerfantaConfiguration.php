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

use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Configuration_Interface;
/**
 * Container configuration to bridge the configuration from WhiteOctoberPagerfantaBundle to BabDevPagerfantaBundle
 *
 * @internal
 */
final class Pagerfanta_Configuration implements Configuration_Interface
{
    public const EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND = 'to_http_not_found';
    /**
     * @return TreeBuilder<'array'>
     */
    public function get_config_tree_builder(): Tree_Builder
    {
        $tree_builder = new Tree_Builder('white_october_pagerfanta');
        /** @var ArrayNodeDefinition<TreeBuilder<'array'>> $rootNode */
        $root_node = $tree_builder->get_root_node();
        $root_node->set_deprecated('sylius/resource-bundle', '1.7', 'The "%node%" configuration node is deprecated, migrate your configuration to the "babdev_pagerfanta" configuration node.');
        $this->add_exceptions_strategy_section($root_node);
        $root_node->children()->scalar_node('default_view')->default_value('default')->end()->end();
        return $tree_builder;
    }
    /**
     * @param ArrayNodeDefinition<TreeBuilder<'array'>> $node
     */
    private function add_exceptions_strategy_section(Array_Node_Definition $node): void
    {
        $node->children()->array_node('exceptions_strategy')->add_defaults_if_not_set()->children()->scalar_node('out_of_range_page')->default_value(self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND)->end()->scalar_node('not_valid_current_page')->default_value(self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND)->end()->end()->end()->end();
    }
}