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

final class Class_Reflection
{
    /**
     * @return \Generator<class-string>
     */
    public static function get_resources_by_paths(array $paths): iterable
    {
        trigger_deprecation('sylius/resource-bundle', '1.14', 'The method "%s" is deprecated, use "%s::%s" instead.', __METHOD__, Reflection_Class_Recursive_Iterator::class, 'getReflectionClassesFromDirectories');
        foreach (Reflection_Class_Recursive_Iterator::get_reflection_classes_from_directories($paths) as $reflection_class) {
            yield $reflection_class->get_name();
        }
    }
    public static function get_resources_by_path(string $path): iterable
    {
        trigger_deprecation('sylius/resource-bundle', '1.14', 'The method "%s" is deprecated, use "%s::%s" instead.', __METHOD__, Reflection_Class_Recursive_Iterator::class, 'getReflectionClassesFromDirectories');
        foreach (Reflection_Class_Recursive_Iterator::get_reflection_classes_from_directories([$path]) as $reflection_class) {
            yield $reflection_class->get_name();
        }
    }
    /**
     * @param class-string $className
     *
     * @return \ReflectionAttribute[]
     */
    public static function get_class_attributes(string $class_name, ?string $attribute_name = null): array
    {
        $reflection_class = new \ReflectionClass($class_name);
        /** @psalm-suppress ArgumentTypeCoercion */
        return $reflection_class->get_attributes($attribute_name);
    }
}
if (!class_exists(\Sylius\Component\Resource\Reflection\Class_Reflection::class, false)) {
    class_alias(Class_Reflection::class, \Sylius\Component\Resource\Reflection\Class_Reflection::class);
}