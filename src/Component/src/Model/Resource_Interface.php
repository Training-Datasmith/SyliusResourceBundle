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
namespace Sylius\Resource\Model;

interface Resource_Interface
{
    /** @psalm-suppress MissingReturnType */
    public function get_id();
}
if (!class_exists(\Sylius\Component\Resource\Model\Resource_Interface::class, false)) {
    class_alias(Resource_Interface::class, \Sylius\Component\Resource\Model\Resource_Interface::class);
}