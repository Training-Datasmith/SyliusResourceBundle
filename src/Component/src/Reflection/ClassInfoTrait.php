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

use Webmozart\Assert\Assert;
/**
 * Retrieves information about a class.
 *
 * @internal
 */
trait Class_Info_Trait
{
    /**
     * Get class name of the given object.
     *
     * @return class-string
     */
    private function get_object_class(object $object): string
    {
        return $this->get_real_class_name($object::class);
    }
    /**
     * Get the real class name of a class name that could be a proxy.
     *
     * @param class-string $className
     *
     * @return class-string
     */
    private function get_real_class_name(string $class_name): string
    {
        // __CG__: Doctrine Common Marker for Proxy (ODM < 2.0 and ORM < 3.0)
        // __PM__: Ocramius Proxy Manager (ODM >= 2.0)
        $position_cg = strrpos($class_name, '\__CG__\\');
        $position_pm = strrpos($class_name, '\__PM__\\');
        if (false === $position_cg && false === $position_pm) {
            return $class_name;
        }
        if (false !== $position_cg) {
            $un_proxied_class_name = substr($class_name, $position_cg + 8);
            Assert::class_exists($un_proxied_class_name);
            return $un_proxied_class_name;
        }
        $class_name = ltrim($class_name, '\\');
        $un_proxied_class_name = substr($class_name, 8 + $position_pm, strrpos($class_name, '\\') - ($position_pm + 8));
        Assert::class_exists($un_proxied_class_name);
        return $un_proxied_class_name;
    }
}