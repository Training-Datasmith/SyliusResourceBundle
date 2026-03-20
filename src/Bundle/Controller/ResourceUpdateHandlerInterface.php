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

use Doctrine\Persistence\Object_Manager;
use Sylius\Resource\Model\Resource_Interface;
interface Resource_Update_Handler_Interface
{
    public function handle(Resource_Interface $resource, Request_Configuration $request_configuration, Object_Manager $manager): void;
}