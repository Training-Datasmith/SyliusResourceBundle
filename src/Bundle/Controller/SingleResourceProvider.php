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
use Sylius\Resource\Model\Resource_Interface;
final class Single_Resource_Provider implements Single_Resource_Provider_Interface
{
    public function get(Request_Configuration $request_configuration, Repository_Interface $repository): ?Resource_Interface
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
        $request = $request_configuration->get_request();
        if ($request->attributes->has('id')) {
            /** @var ResourceInterface|null $resource */
            $resource = $repository->find($request->attributes->get('id'));
            return $resource;
        }
        if ($request->attributes->has('slug')) {
            $criteria = ['slug' => $request->attributes->get('slug')];
        }
        $criteria = array_merge($criteria, $request_configuration->get_criteria());
        /** @var ResourceInterface|null $resource */
        $resource = $repository->find_one_by($criteria);
        return $resource;
    }
}