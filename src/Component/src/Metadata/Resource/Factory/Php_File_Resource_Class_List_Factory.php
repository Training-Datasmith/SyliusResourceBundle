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

use Sylius\Resource\Metadata\Extractor\Resource_Extractor_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Class_List;
/**
 * Creates a resource class list from PHP configuration files.
 *
 * @experimental
 */
final readonly class Php_File_Resource_Class_List_Factory implements Resource_Class_List_Factory_Interface
{
    public function __construct(private Resource_Extractor_Interface $php_file_resource_metadata_extractor, private ?Resource_Class_List_Factory_Interface $decorated = null)
    {
    }
    public function create(): Resource_Class_List
    {
        $classes = [];
        if ($this->decorated) {
            foreach ($this->decorated->create() as $resource_class) {
                $classes[$resource_class] = true;
            }
        }
        foreach ($this->php_file_resource_metadata_extractor->get_resources() as $resource) {
            $resource_class = $resource->get_class();
            if (null === $resource_class) {
                continue;
            }
            $classes[$resource_class] = true;
        }
        return new Resource_Class_List(array_keys($classes));
    }
}