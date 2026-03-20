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

interface Versioned_Interface
{
    public function get_version(): ?int;
    public function set_version(?int $version): void;
}
if (!class_exists(\Sylius\Component\Resource\Model\Versioned_Interface::class, false)) {
    class_alias(Versioned_Interface::class, \Sylius\Component\Resource\Model\Versioned_Interface::class);
}