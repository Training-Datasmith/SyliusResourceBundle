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
interface New_Resource_Factory_Interface
{
    public function create(Request_Configuration $request_configuration, Factory_Interface $factory): Resource_Interface;
}