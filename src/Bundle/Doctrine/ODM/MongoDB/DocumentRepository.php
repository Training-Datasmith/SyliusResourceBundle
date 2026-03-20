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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ODM\Mongo_Db;

use Doctrine\Mongo_Db\Query\Builder as QueryBuilder;
use Doctrine\ODM\Mongo_Db\Document_Repository as BaseDocumentRepository;
use Pagerfanta\Doctrine\Mongo_Dbodm\Query_Adapter;
use Pagerfanta\Pagerfanta;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Model\Resource_Interface;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Document_Repository::class);
/**
 * Doctrine ODM driver resource manager.
 */
class Document_Repository extends Base_Document_Repository implements Repository_Interface
{
    /**
     * @param int $id
     */
    public function find($id): object
    {
        return $this->get_query_builder()->field('id')->equals(new \Mongo_Id($id))->get_query()->get_single_result();
    }
    public function find_all(): iterable
    {
        return $this->get_collection_query_builder()->get_query()->getIterator();
    }
    public function find_one_by(array $criteria): object
    {
        $query_builder = $this->get_query_builder();
        $this->apply_criteria($query_builder, $criteria);
        return $query_builder->get_query()->get_single_result();
    }
    /**
     * @param int $limit
     * @param int $offset
     */
    public function find_by(array $criteria, ?array $sorting = null, $limit = null, $offset = null): iterable
    {
        $query_builder = $this->get_collection_query_builder();
        $this->apply_criteria($query_builder, $criteria);
        $this->apply_sorting($query_builder, $sorting);
        if (null !== $limit) {
            $query_builder->limit($limit);
        }
        if (null !== $offset) {
            $query_builder->skip($offset);
        }
        return $query_builder->get_query()->getIterator();
    }
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
    protected function get_query_builder(): Query_Builder
    {
        return $this->create_query_builder();
    }
    protected function get_collection_query_builder(): Query_Builder
    {
        return $this->create_query_builder();
    }
    protected function apply_criteria(Query_Builder $query_builder, array $criteria = []): void
    {
        foreach ($criteria as $property => $value) {
            $query_builder->field($property)->equals($value);
        }
    }
    protected function apply_sorting(Query_Builder $query_builder, array $sorting = []): void
    {
        foreach ($sorting as $property => $order) {
            $query_builder->sort($property, $order);
        }
    }
}