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
interface Resources_Collection_Provider_Interface
{
    /** @psalm-suppress MissingReturnType */
    public function get(Request_Configuration $request_configuration, Repository_Interface $repository);
}