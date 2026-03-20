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
namespace Sylius\Resource\Metadata\Resource\Factory;

use Sylius\Resource\Metadata\As_Resource;
use Sylius\Resource\Metadata\Resource\Resource_Class_List;
use Sylius\Resource\Reflection\Class_Reflection;
use Sylius\Resource\Reflection\Reflection_Class_Recursive_Iterator;
/**
 * Creates a resource class list from {@see AsResource} attributes.
 *
 * @experimental
 */
final readonly class Attributes_Resource_Class_List_Factory implements Resource_Class_List_Factory_Interface
{
    /** @param array{paths: string[]} $mapping */
    public function __construct(private array $mapping, private ?Resource_Class_List_Factory_Interface $decorated = null)
    {
    }
    /**
     * @inheritdoc
     */
    public function create(): Resource_Class_List
    {
        $classes = [];
        if ($this->decorated) {
            foreach ($this->decorated->create() as $resource_class) {
                $classes[$resource_class] = true;
            }
        }
        $paths = $this->mapping['paths'] ?? [];
        foreach (Reflection_Class_Recursive_Iterator::get_reflection_classes_from_directories($paths) as $reflection_class) {
            $resource_class = $reflection_class->get_name();
            if ([] === Class_Reflection::get_class_attributes($resource_class, As_Resource::class)) {
                continue;
            }
            $classes[$resource_class] = true;
        }
        return new Resource_Class_List(array_keys($classes));
    }
}