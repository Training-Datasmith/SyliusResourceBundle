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

use Sylius\Resource\Factory\Factory_Interface;
use Sylius\Resource\Model\Resource_Interface;
final class New_Resource_Factory implements New_Resource_Factory_Interface
{
    public function create(Request_Configuration $request_configuration, Factory_Interface $factory): Resource_Interface
    {
        if (null === $method = $request_configuration->get_factory_method()) {
            /** @var ResourceInterface $resource */
            $resource = $factory->create_new();
            return $resource;
        }
        if (is_array($method) && 2 === count($method)) {
            $factory = $method[0];
            $method = $method[1];
        }
        $arguments = array_values($request_configuration->get_factory_arguments());
        return $factory->{$method}(...$arguments);
    }
}