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
namespace Sylius\Resource;

final class Resource_Actions
{
    public const SHOW = 'show';
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const BULK_DELETE = 'bulk_delete';
    private function __construct()
    {
    }
}
if (!class_exists(\Sylius\Component\Resource\Resource_Actions::class, false)) {
    class_alias(Resource_Actions::class, \Sylius\Component\Resource\Resource_Actions::class);
}