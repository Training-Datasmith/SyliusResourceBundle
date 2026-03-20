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

interface Toggleable_Interface
{
    /**
     * Missing scalar typehint because it conflicts with AdvancedUserInterface.
     *
     * @return bool
     */
    public function is_enabled();
    public function set_enabled(?bool $enabled): void;
    public function enable(): void;
    public function disable(): void;
}
if (!class_exists(\Sylius\Component\Resource\Model\Toggleable_Interface::class, false)) {
    class_alias(Toggleable_Interface::class, \Sylius\Component\Resource\Model\Toggleable_Interface::class);
}