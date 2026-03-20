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
namespace Sylius\Resource\Doctrine\Persistence\Exception;

class Resource_Exists_Exception extends \RuntimeException implements Exception_Interface
{
    public function __construct()
    {
        parent::__construct('Given resource already exists in the repository.');
    }
}
if (!class_exists(\Sylius\Component\Resource\Repository\Exception\Existing_Resource_Exception::class, false)) {
    class_alias(Resource_Exists_Exception::class, \Sylius\Component\Resource\Repository\Exception\Existing_Resource_Exception::class);
}