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
namespace Sylius\Resource\Symfony\Routing\Factory\Route_Path;

use Sylius\Resource\Metadata\Http_Operation;
/**
 * @experimental
 */
interface Operation_Route_Path_Factory_Interface
{
    public function create_route_path(Http_Operation $operation, string $root_path): string;
}