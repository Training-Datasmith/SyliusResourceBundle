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

interface Code_Aware_Interface
{
    public function get_code(): ?string;
    public function set_code(?string $code): void;
}
if (!class_exists(\Sylius\Component\Resource\Model\Code_Aware_Interface::class, false)) {
    class_alias(Code_Aware_Interface::class, \Sylius\Component\Resource\Model\Code_Aware_Interface::class);
}