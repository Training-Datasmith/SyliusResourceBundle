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
namespace Sylius\Bundle\Resource_Bundle\Grid\Parser;

use Sylius\Bundle\Resource_Bundle\Controller\Bc_Layer_Request_Trait;
use Symfony\Component\Dependency_Injection\Container_Interface;
use Symfony\Component\Expression_Language\Expression_Language;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Property_Access\Property_Accessor_Interface;
final class Options_Parser implements Options_Parser_Interface
{
    use Bc_Layer_Request_Trait;
    private Container_Interface $container;
    private Expression_Language $expression;
    private Property_Accessor_Interface $property_accessor;
    public function __construct(Container_Interface $container, Expression_Language $expression, Property_Accessor_Interface $property_accessor)
    {
        $this->container = $container;
        $this->expression = $expression;
        $this->property_accessor = $property_accessor;
    }
    /**
     * @param array|object|null $data
     */
    public function parse_options(array $parameters, Request $request, $data = null): array
    {
        return array_map(
            /**
             * @param mixed $parameter
             *
             * @return mixed
             */
            function ($parameter) use ($request, $data) {
                if (is_array($parameter)) {
                    return $this->parse_options($parameter, $request, $data);
                }
                return $this->parse_option($parameter, $request, $data);
            },
            $parameters
        );
    }
    /**
     * @param mixed $parameter
     * @param array|object|null $data
     *
     * @return mixed
     */
    private function parse_option($parameter, Request $request, $data)
    {
        if (!is_string($parameter)) {
            return $parameter;
        }
        if (str_starts_with($parameter, '$')) {
            return $this->get_from_request($request, substr($parameter, 1));
        }
        if (str_starts_with($parameter, 'expr:')) {
            return $this->parse_option_expression(substr($parameter, 5), $request);
        }
        if (str_starts_with($parameter, 'resource.')) {
            return $this->parse_option_resource_field(substr($parameter, 9), $data);
        }
        if (str_starts_with($parameter, 'resource[')) {
            return $this->parse_option_resource_field(substr($parameter, 8), $data);
        }
        return $parameter;
    }
    /**
     * @return mixed
     */
    private function parse_option_expression(string $expression, Request $request)
    {
        $expression = (string) preg_replace_callback(
            '/\$(\w+)/',
            /** @return callable */
            function (array $matches) use ($request) {
                $variable = $this->get_from_request($request, $matches[1]);
                return is_string($variable) ? sprintf('"%s"', addslashes($variable)) : $variable;
            },
            $expression
        );
        return $this->expression->evaluate($expression, ['container' => $this->container]);
    }
    /**
     * @param array|object|null $data
     *
     * @return mixed
     */
    private function parse_option_resource_field(string $value, $data)
    {
        return $this->property_accessor->get_value($data, $value);
    }
}