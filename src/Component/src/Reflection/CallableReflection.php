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
namespace Sylius\Resource\Reflection;

final class Callable_Reflection
{
    public static function from(callable $callable): \Reflection_Function_Abstract
    {
        if ($callable instanceof \Closure) {
            return new \ReflectionFunction($callable);
        }
        if (is_string($callable)) {
            $callable_parts = explode('::', $callable);
            /** @psalm-var class-string $object */
            $object = $callable_parts[0];
            return count($callable_parts) > 1 ? new \ReflectionMethod($object, $callable_parts[1]) : new \ReflectionFunction($callable);
        }
        if (!is_array($callable)) {
            $callable = [$callable, '__invoke'];
        }
        return new \ReflectionMethod($callable[0], $callable[1]);
    }
}