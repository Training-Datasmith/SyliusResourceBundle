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

use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Http_Foundation\Request;
interface Request_Configuration_Factory_Interface
{
    /**
     * @throws \InvalidArgumentException
     */
    public function create(Metadata_Interface $metadata, Request $request): Request_Configuration;
}