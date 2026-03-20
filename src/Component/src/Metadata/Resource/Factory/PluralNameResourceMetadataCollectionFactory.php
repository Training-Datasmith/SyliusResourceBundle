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

use Sylius\Resource\Exception\LogicException;
use Sylius\Resource\Metadata\Inflector\Inflector_Interface;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
/**
 * @experimental
 */
final readonly class Plural_Name_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private Resource_Metadata_Collection_Factory_Interface $decorated, private Inflector_Interface $inflector, private bool $routing_bc_layer_enabled = true, private ?Registry_Interface $resource_registry = null)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_collection_metadata = $this->decorated->create($resource_class);
        /** @var ResourceMetadata $resource */
        foreach ($resource_collection_metadata->getIterator() as $i => $resource) {
            $resource_collection_metadata[$i] = $this->add_defaults($resource);
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Resource_Metadata $resource): Resource_Metadata
    {
        if (null !== $resource->get_plural_name()) {
            return $resource;
        }
        if ($this->routing_bc_layer_enabled) {
            if (null === $this->resource_registry) {
                throw new LogicException(sprintf('Routing Bc-Layer is enabled, but the resource registry is not passed as constructor arguments of "%s" class.', self::class));
            }
            $resource_configuration = $this->resource_registry->get($resource->get_alias() ?? '');
            return $resource->with_plural_name($resource_configuration->get_plural_name());
        }
        /**
         * Resource name has already been configured.
         *
         * @see OperationDefaultsTrait
         *
         * @var string $resourceName
         */
        $resource_name = $resource->get_name();
        return $resource->with_plural_name($this->inflector->pluralize($resource_name));
    }
}