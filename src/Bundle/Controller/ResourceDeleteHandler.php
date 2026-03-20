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
final class Resource_Delete_Handler implements Resource_Delete_Handler_Interface
{
    public function handle(Resource_Interface $resource, Repository_Interface $repository): void
    {
        $repository->remove($resource);
    }
}