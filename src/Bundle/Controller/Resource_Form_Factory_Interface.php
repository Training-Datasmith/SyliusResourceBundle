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

use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Form\Form_Interface;
interface Resource_Form_Factory_Interface
{
    public function create(Request_Configuration $request_configuration, Resource_Interface $resource): Form_Interface;
}