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
namespace Sylius\Bundle\Resource_Bundle\Doctrine;

use Doctrine\Persistence\Mapping\Class_Metadata;
use Doctrine\Persistence\Mapping\Driver\Mapping_Driver;
use Doctrine\Persistence\Mapping\Driver\Mapping_Driver_Chain;
use Sylius\Resource\Metadata\Registry_Interface;
/**
 * It needs to extend MappingDriverChain in order to be compatible with Gedmo/DoctrineExtensions.
 *
 * @see \Gedmo\Mapping\ExtensionMetadataFactory::getDriver()
 */
final class Resource_Mapping_Driver_Chain extends Mapping_Driver_Chain
{
    public function __construct(Mapping_Driver $mapping_driver, private readonly Registry_Interface $resource_registry)
    {
        $this->set_default_driver($mapping_driver);
    }
    public function load_metadata_for_class($class_name, Class_Metadata $metadata): void
    {
        parent::load_metadata_for_class($class_name, $metadata);
        $this->convert_resource_mapped_superclass($metadata);
    }
    /**
     * @psalm-suppress NoInterfaceProperties https://github.com/vimeo/psalm/issues/2206
     */
    private function convert_resource_mapped_superclass(Class_Metadata $metadata): void
    {
        if (!isset($metadata->is_mapped_superclass)) {
            return;
        }
        if (false === $metadata->is_mapped_superclass) {
            return;
        }
        try {
            $resource_metadata = $this->resource_registry->get_by_class($metadata->get_name());
        } catch (\InvalidArgumentException) {
            return;
        }
        if ($metadata->get_name() !== $resource_metadata->get_class('model')) {
            return;
        }
        $metadata->is_mapped_superclass = false;
    }
}