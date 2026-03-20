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
namespace Sylius\Resource\Reflection\Filter;

final class Function_Arguments_Filter
{
    public static function filter(\Reflection_Function_Abstract $reflection_function, array $arguments): array
    {
        $repository_arguments = self::get_function_arguments($reflection_function);
        $allowed = array_intersect_key($repository_arguments, $arguments);
        return array_intersect_key($arguments, array_flip(array_keys($allowed)));
    }
    private static function get_function_arguments(\Reflection_Function_Abstract $reflection_function): array
    {
        $arguments = [];
        foreach ($reflection_function->get_parameters() as $param) {
            $arguments[$param->name] = null;
        }
        return $arguments;
    }
}