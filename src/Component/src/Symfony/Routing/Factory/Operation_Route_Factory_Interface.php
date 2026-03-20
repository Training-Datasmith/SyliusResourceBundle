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
namespace Sylius\Resource\Symfony\Routing\Factory;

use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Resource_Metadata;
use Symfony\Component\Routing\Route;
/**
 * @experimental
 */
interface Operation_Route_Factory_Interface
{
    public function create(Metadata_Interface $metadata, Resource_Metadata $resource, Http_Operation $operation): Route;
}