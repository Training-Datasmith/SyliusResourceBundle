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

/**
 * This authorization checker always returns true. Useful if you don't want to have authorization checks at all.
 */
final class Disabled_Authorization_Checker implements Authorization_Checker_Interface
{
    public function is_granted(Request_Configuration $configuration, string $permission): bool
    {
        return true;
    }
}