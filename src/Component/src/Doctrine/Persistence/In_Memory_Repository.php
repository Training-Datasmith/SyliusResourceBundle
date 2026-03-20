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
namespace Sylius\Resource\Doctrine\Persistence;

use Pagerfanta\Adapter\Array_Adapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Pagerfanta_Interface;
use Sylius\Component\Resource\Exception\Unexpected_Type_Exception;
use Sylius\Resource\Doctrine\Persistence\Exception\Resource_Exists_Exception;
use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Property_Access\Property_Access;
use Symfony\Component\Property_Access\Property_Accessor;
use Webmozart\Assert\Assert;
class In_Memory_Repository implements Repository_Interface
{
    protected Property_Accessor $accessor;
    protected \ArrayObject $array_object;
    /** @psalm-var class-string */
    protected string $interface;
    /**
     * @psalm-param class-string $interface
     *
     * @throws \InvalidArgumentException
     * @throws UnexpectedTypeException
     */
    public function __construct(string $interface)
    {
        /** @var array $interfaceInterfaces */
        $interface_interfaces = class_implements($interface);
        if (!in_array(Resource_Interface::class, $interface_interfaces, true)) {
            throw new Unexpected_Type_Exception($interface, Resource_Interface::class);
        }
        $this->interface = $interface;
        $this->accessor = Property_Access::create_property_accessor();
        $this->array_object = new \ArrayObject();
    }
    /**
     * @throws ResourceExistsException
     * @throws UnexpectedTypeException
     */
    public function add(Resource_Interface $resource): void
    {
        if (!$resource instanceof $this->interface) {
            throw new Unexpected_Type_Exception($resource, $this->interface);
        }
        if (in_array($resource, $this->find_all(), true)) {
            throw new Resource_Exists_Exception();
        }
        $this->array_object->append($resource);
    }
    public function remove(Resource_Interface $resource): void
    {
        $new_resources = array_filter($this->find_all(), static fn($object) => $object !== $resource);
        $this->array_object->exchange_array($new_resources);
    }
    public function find($id): ?object
    {
        return $this->find_one_by(['id' => $id]);
    }
    public function find_all(): array
    {
        $array_copy = $this->array_object->get_array_copy();
        Assert::all_object($array_copy);
        return $array_copy;
    }
    public function find_by(array $criteria, ?array $order_by = null, $limit = null, $offset = null): array
    {
        $results = $this->find_all();
        if (!empty($criteria)) {
            $results = $this->apply_criteria($results, $criteria);
        }
        if (!empty($order_by)) {
            $results = $this->apply_order($results, $order_by);
        }
        return array_slice($results, $offset ?? 0, $limit);
    }
    /**
     * @throws \InvalidArgumentException
     */
    public function find_one_by(array $criteria): ?Resource_Interface
    {
        if (empty($criteria)) {
            throw new \InvalidArgumentException('The criteria array needs to be set.');
        }
        $results = $this->apply_criteria($this->find_all(), $criteria);
        /** @var ResourceInterface|false $result */
        $result = reset($results);
        if ($result !== false) {
            return $result;
        }
        return null;
    }
    public function get_class_name(): string
    {
        return $this->interface;
    }
    /**
     * @return PagerfantaInterface
     */
    public function create_paginator(array $criteria = [], array $sorting = []): iterable
    {
        $resources = $this->find_all();
        if (!empty($sorting)) {
            $resources = $this->apply_order($resources, $sorting);
        }
        if (!empty($criteria)) {
            $resources = $this->apply_criteria($resources, $criteria);
        }
        return new Pagerfanta(new Array_Adapter($resources));
    }
    /**
     * @param object[] $resources
     *
     * @return object[]|array
     */
    private function apply_criteria(array $resources, array $criteria): array
    {
        /** @var array|object $object */
        foreach ($this->array_object as $object) {
            foreach ($criteria as $criterion => $value) {
                if ($value !== $this->accessor->get_value($object, $criterion)) {
                    $key = array_search($object, $resources);
                    unset($resources[$key]);
                }
            }
        }
        return $resources;
    }
    /**
     * @param object[] $resources
     *
     * @return object[]
     */
    private function apply_order(array $resources, array $order_by): array
    {
        $results = $resources;
        $arguments = [];
        foreach ($order_by as $property => $order) {
            $sortable = [];
            foreach ($results as $key => $object) {
                $sortable[$key] = $this->accessor->get_value($object, $property);
            }
            $arguments[] = $sortable;
            if (Repository_Interface::ORDER_ASCENDING === $order) {
                $arguments[] = \SORT_ASC;
            } elseif (Repository_Interface::ORDER_DESCENDING === $order) {
                $arguments[] = \SORT_DESC;
            } else {
                throw new \InvalidArgumentException('Unknown order.');
            }
        }
        $arguments[] =& $results;
        /**
         * Doing PHP magic, it works this way
         *
         * @psalm-suppress InvalidPassByReference
         * @psalm-suppress PossiblyInvalidArgument
         */
        array_multisort(...$arguments);
        return $results;
    }
}
if (!class_exists(\Sylius\Component\Resource\Repository\In_Memory_Repository::class, false)) {
    class_alias(In_Memory_Repository::class, \Sylius\Component\Resource\Repository\In_Memory_Repository::class);
}