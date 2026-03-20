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
namespace Sylius\Bundle\Resource_Bundle\Controller;

use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
final class Resources_Resolver implements Resources_Resolver_Interface
{
    /**
     * @psalm-suppress MissingReturnType
     */
    public function get_resources(Request_Configuration $request_configuration, Repository_Interface $repository)
    {
        $method = $request_configuration->get_repository_method();
        if (null !== $method) {
            if (is_array($method) && 2 === count($method)) {
                $repository = $method[0];
                $method = $method[1];
            }
            $arguments = array_values($request_configuration->get_repository_arguments());
            return $repository->{$method}(...$arguments);
        }
        $criteria = [];
        if ($request_configuration->is_filterable()) {
            $criteria = $request_configuration->get_criteria();
        }
        $sorting = [];
        if ($request_configuration->is_sortable()) {
            $sorting = $request_configuration->get_sorting();
        }
        if ($request_configuration->is_paginated()) {
            return $repository->create_paginator($criteria, $sorting);
        }
        return $repository->find_by($criteria, $sorting, $request_configuration->get_limit());
    }
}