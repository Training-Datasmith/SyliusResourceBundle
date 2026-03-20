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
namespace Sylius\Resource\Factory;

/**
 * @template T of object
 */
interface Factory_Interface
{
    /**
     * @return T
     */
    public function create_new();
}
if (!class_exists(\Sylius\Component\Resource\Factory\Factory_Interface::class, false)) {
    class_alias(Factory_Interface::class, \Sylius\Component\Resource\Factory\Factory_Interface::class);
}