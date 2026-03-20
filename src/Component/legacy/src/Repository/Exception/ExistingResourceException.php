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
namespace Sylius\Component\Resource\Repository\Exception;

class_exists(\Sylius\Resource\Doctrine\Persistence\Exception\Resource_Exists_Exception::class);
if (false) {
    class Existing_Resource_Exception extends \Sylius\Resource\Doctrine\Persistence\Exception\Resource_Exists_Exception
    {
    }
}