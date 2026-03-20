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
use Sylius\Component\Resource\Repository\Translatable_Repository_Interface;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Translatable_Repository::class);
/**
 * Doctrine ORM driver translatable entity repository.
 */
class Translatable_Repository extends Document_Repository implements Translatable_Repository_Interface
{
    protected function apply_criteria(Query_Builder $query_builder, ?array $criteria = null): void
    {
        if (null === $criteria) {
            return;
        }
        foreach ($criteria as $property => $value) {
            if (is_array($value)) {
                $query_builder->field($property)->in($value);
            } elseif ('' !== $value) {
                $query_builder->field($property)->equals($value);
            }
        }
    }
    protected function apply_sorting(Query_Builder $query_builder, ?array $sorting = null): void
    {
        if (null === $sorting) {
            return;
        }
        foreach ($sorting as $property => $order) {
            $query_builder->sort($property, $order);
        }
    }
}