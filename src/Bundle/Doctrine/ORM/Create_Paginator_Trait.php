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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ORM;

use Doctrine\ORM\Entity_Repository as DoctrineEntityRepository;
use Doctrine\ORM\Query_Builder;
use Pagerfanta\Adapter\Array_Adapter;
use Pagerfanta\Doctrine\ORM\Query_Adapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Pagerfanta_Interface;
use Sylius\Resource\Model\Resource_Interface;
/**
 * @mixin DoctrineEntityRepository
 */
trait Create_Paginator_Trait
{
    /**
     * @return iterable<int, ResourceInterface>
     */
    public function create_paginator(array $criteria = [], array $sorting = []): iterable
    {
        $query_builder = $this->create_query_builder('o');
        $this->apply_criteria($query_builder, $criteria);
        $this->apply_sorting($query_builder, $sorting);
        return $this->get_paginator($query_builder);
    }
    protected function get_paginator(Query_Builder $query_builder): Pagerfanta_Interface
    {
        if (!class_exists(Query_Adapter::class)) {
            throw new \LogicException('You can not use the "paginator" if Pargefanta Doctrine ORM Adapter is not available. Try running "composer require pagerfanta/doctrine-orm-adapter".');
        }
        // Use output walkers option in the query adapter should be false as it affects performance greatly (see sylius/sylius#3775)
        return new Pagerfanta(new Query_Adapter($query_builder, false, false));
    }
    /**
     * @param array $objects
     */
    protected function get_array_paginator($objects): Pagerfanta_Interface
    {
        return new Pagerfanta(new Array_Adapter($objects));
    }
    protected function apply_criteria(Query_Builder $query_builder, array $criteria = []): void
    {
        foreach ($criteria as $property => $value) {
            if (!in_array($property, array_merge($this->get_class_metadata()->get_association_names(), $this->get_class_metadata()->get_field_names()), true)) {
                continue;
            }
            $name = $this->get_property_name($property);
            if (null === $value) {
                $query_builder->and_where($query_builder->expr()->is_null($name));
            } elseif (is_array($value)) {
                $query_builder->and_where($query_builder->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $parameter = str_replace('.', '_', $property);
                $query_builder->and_where($query_builder->expr()->eq($name, ':' . $parameter))->set_parameter($parameter, $value);
            }
        }
    }
    protected function apply_sorting(Query_Builder $query_builder, array $sorting = []): void
    {
        foreach ($sorting as $property => $order) {
            if (!in_array($property, array_merge($this->get_class_metadata()->get_association_names(), $this->get_class_metadata()->get_field_names()), true)) {
                continue;
            }
            if (!empty($order)) {
                $query_builder->add_order_by($this->get_property_name($property), $order);
            }
        }
    }
    protected function get_property_name(string $name): string
    {
        if (!str_contains($name, '.')) {
            return 'o' . '.' . $name;
        }
        return $name;
    }
}