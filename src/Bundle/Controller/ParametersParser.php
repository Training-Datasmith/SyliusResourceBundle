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
namespace Sylius\Bundle\Resource_Bundle\Controller;

use Sylius\Bundle\Resource_Bundle\Provider\Request_Parameter_Provider;
use Symfony\Component\Dependency_Injection\Container_Interface;
use Symfony\Component\Expression_Language\Expression_Language;
use Symfony\Component\Http_Foundation\Request;
use Webmozart\Assert\Assert;
final class Parameters_Parser implements Parameters_Parser_Interface
{
    use Bc_Layer_Request_Trait;
    private Container_Interface $container;
    private Expression_Language $expression;
    public function __construct(Container_Interface $container, Expression_Language $expression)
    {
        $this->container = $container;
        $this->expression = $expression;
    }
    public function parse_request_values(array $parameters, Request $request): array
    {
        return array_map(
            /**
             * @param mixed $parameter
             *
             * @return mixed
             */
            function ($parameter) use ($request) {
                if (is_array($parameter)) {
                    return $this->parse_request_values($parameter, $request);
                }
                return $this->parse_request_value($parameter, $request);
            },
            $parameters
        );
    }
    /**
     * @param mixed $parameter
     *
     * @return mixed
     */
    private function parse_request_value($parameter, Request $request)
    {
        if (!is_string($parameter)) {
            return $parameter;
        }
        if (str_starts_with($parameter, '$')) {
            return Request_Parameter_Provider::provide($request, substr($parameter, 1));
        }
        if (str_starts_with($parameter, 'expr:')) {
            return $this->parse_request_value_expression(substr($parameter, 5), $request);
        }
        if (str_starts_with($parameter, '!!')) {
            return $this->parse_request_value_typecast($parameter, $request);
        }
        return $parameter;
    }
    /** @return mixed */
    private function parse_request_value_expression(string $expression, Request $request)
    {
        $expression = (string) preg_replace_callback(
            '/(\$\w+)/',
            /**
             * @return mixed
             */
            function (array $matches) use ($request) {
                $variable = $this->get_from_request($request, substr($matches[1], 1));
                if (is_array($variable) || is_object($variable)) {
                    throw new \InvalidArgumentException(sprintf('Cannot use %s ($%s) as parameter in expression.', gettype($variable), $matches[1]));
                }
                return is_string($variable) ? sprintf('"%s"', addslashes($variable)) : $variable;
            },
            $expression
        );
        return $this->expression->evaluate($expression, ['container' => $this->container]);
    }
    /** @return mixed */
    private function parse_request_value_typecast(string $parameter, Request $request)
    {
        [$typecast, $casted_value] = explode(' ', $parameter, 2);
        /** @var callable $castFunctionName */
        $cast_function_name = substr($typecast, 2) . 'val';
        Assert::one_of($cast_function_name, ['intval', 'floatval', 'boolval'], 'Variable can be casted only to int, float or bool.');
        return $cast_function_name($this->parse_request_value($casted_value, $request));
    }
}