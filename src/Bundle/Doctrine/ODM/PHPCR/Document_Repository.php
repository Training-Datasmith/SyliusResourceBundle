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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR;

use Doctrine\ODM\PHPCR\Document_Repository as BaseDocumentRepository;
use Doctrine\ODM\PHPCR\Query\Builder\Query_Builder;
use Pagerfanta\Doctrine\PHPCRODM\Query_Adapter;
use Pagerfanta\Pagerfanta;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Model\Resource_Interface;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Document_Repository::class);
/**
 * Doctrine PHPCR-ODM driver document repository.
 */
class Document_Repository extends Base_Document_Repository implements Repository_Interface
{
    public function create_paginator(array $criteria = [], array $sorting = []): iterable
    {
        $query_builder = $this->get_collection_query_builder();
        $this->apply_criteria($query_builder, $criteria);
        $this->apply_sorting($query_builder, $sorting);
        return $this->get_paginator($query_builder);
    }
    public function add(Resource_Interface $resource): void
    {
        $this->dm->persist($resource);
        $this->dm->flush();
    }
    public function remove(Resource_Interface $resource): void
    {
        if (null !== $this->find($resource->get_id())) {
            $this->dm->remove($resource);
            $this->dm->flush();
        }
    }
    public function get_paginator(Query_Builder $query_builder): Pagerfanta
    {
        return new Pagerfanta(new Query_Adapter($query_builder));
    }
    protected function get_collection_query_builder(): Query_Builder
    {
        return $this->create_query_builder('o');
    }
    protected function apply_criteria(Query_Builder $query_builder, array $criteria = []): void
    {
        $metadata = $this->get_class_metadata();
        foreach ($criteria as $property => $value) {
            if (!empty($value)) {
                if ($property === $metadata->nodename) {
                    $query_builder->and_where()->eq()->local_name($this->get_alias())->literal($value);
                } else {
                    $query_builder->and_where()->eq()->field($this->get_property_name($property))->literal($value);
                }
            }
        }
    }
    protected function apply_sorting(Query_Builder $query_builder, array $sorting = []): void
    {
        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                $query_builder->order_by()->{$order}()->field('o.' . $property);
            }
        }
        $query_builder->end();
    }
    protected function get_property_name(string $name): string
    {
        if (!str_contains($name, '.')) {
            return $this->get_alias() . '.' . $name;
        }
        return $name;
    }
    protected function get_alias(): string
    {
        return 'o';
    }
}