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
namespace Sylius\Resource\Symfony\Request;

use Sylius\Resource\Reflection\Filter\Function_Arguments_Filter;
use Symfony\Component\Http_Foundation\Request;
/**
 * @experimental
 */
final class Repository_Argument_Resolver
{
    public function get_arguments(Request $request, \Reflection_Function_Abstract $reflector): array
    {
        $all_arguments = [$request->attributes->all('_route_params'), $request->query->all(), $request->request->all()];
        foreach ($all_arguments as $arguments) {
            $matched_arguments = Function_Arguments_Filter::filter($reflector, $arguments);
            if (0 === count($matched_arguments) && $this->has_only_one_required_array_parameter($reflector)) {
                $arguments = $this->filter_private_arguments($arguments);
                return [$arguments];
            }
            if ('__call' === $reflector->get_name()) {
                $arguments = $this->filter_private_arguments($arguments);
                if ([] === $arguments) {
                    continue;
                }
                return array_values($arguments);
            }
            if ([] === $matched_arguments) {
                continue;
            }
            return $matched_arguments;
        }
        return [];
    }
    /**
     * @param array<string, mixed> $arguments
     */
    private function filter_private_arguments(array $arguments): array
    {
        return array_filter($arguments, fn(string $key): bool => !str_starts_with($key, '_'), \ARRAY_FILTER_USE_KEY);
    }
    private function has_only_one_required_array_parameter(\Reflection_Function_Abstract $reflector): bool
    {
        /** @var array|\ReflectionParameter[] $parameters */
        $parameters = $reflector->get_parameters();
        $parameters = array_filter($parameters, fn(\ReflectionParameter $parameter): bool => !$parameter->is_default_value_available());
        if (1 !== \count($parameters)) {
            return false;
        }
        $parameter_type = $parameters[0]->get_type()?->__toString();
        return 'array' === $parameter_type;
    }
}