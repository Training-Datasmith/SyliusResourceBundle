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

use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Delete_Operation_Interface;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Metadata\Update_Operation_Interface;
use Sylius\Resource\Symfony\Routing\Factory\Route_Name\Operation_Route_Name_Factory;
final readonly class Redirect_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private Operation_Route_Name_Factory $operation_route_name_factory, private Resource_Metadata_Collection_Factory_Interface $decorated)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_collection_metadata = $this->decorated->create($resource_class);
        /** @var ResourceMetadata $resource */
        foreach ($resource_collection_metadata->getIterator() as $i => $resource) {
            $operations = $resource->get_operations() ?? new Operations();
            /** @var Operation $operation */
            foreach ($operations as $operation) {
                if (!$operation instanceof Http_Operation) {
                    continue;
                }
                /** @var string $key */
                $key = $operation->get_name();
                $operations->add($key, $this->add_defaults($resource, $operation));
            }
            $resource = $resource->with_operations($operations);
            $resource_collection_metadata[$i] = $resource;
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Resource_Metadata $resource, Http_Operation $operation): Operation
    {
        if (null !== $operation->get_redirect_to_route()) {
            return $operation;
        }
        if ($operation instanceof Bulk_Operation_Interface) {
            $new_operation = $this->set_redirect_if_route_exists($resource, $operation, 'index');
            if (null !== $new_operation) {
                return $new_operation;
            }
        }
        if ($operation instanceof Create_Operation_Interface || $operation instanceof Update_Operation_Interface) {
            $new_operation = $this->set_redirect_if_route_exists($resource, $operation, 'show');
            if (null !== $new_operation) {
                return $new_operation;
            }
            $new_operation = $this->set_redirect_if_route_exists($resource, $operation, 'index');
            if (null !== $new_operation) {
                return $new_operation;
            }
        }
        if ($operation instanceof Delete_Operation_Interface) {
            $new_operation = $this->set_redirect_if_route_exists($resource, $operation, 'index');
            if (null !== $new_operation) {
                return $new_operation;
            }
        }
        return $operation;
    }
    private function set_redirect_if_route_exists(Resource_Metadata $resource, Http_Operation $operation, string $short_name): ?Operation
    {
        $route_name = $this->operation_route_name_factory->create_route_name($operation, $short_name);
        if ($resource->has_operation($route_name)) {
            return $operation->with_redirect_to_route($route_name);
        }
        return null;
    }
}