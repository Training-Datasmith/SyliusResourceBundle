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

use Doctrine\ORM\Query_Builder;
interface Resource_Log_Entry_Repository_Interface
{
    public function create_by_object_id_query_builder(string $object_id): Query_Builder;
}