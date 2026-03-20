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

trait Toggleable_Trait
{
    /** @var bool */
    protected $enabled = true;
    public function is_enabled(): bool
    {
        return $this->enabled;
    }
    public function set_enabled(?bool $enabled): void
    {
        $this->enabled = (bool) $enabled;
    }
    public function enable(): void
    {
        $this->enabled = true;
    }
    public function disable(): void
    {
        $this->enabled = false;
    }
}
if (!class_exists(\Sylius\Component\Resource\Model\Toggleable_Trait::class, false)) {
    class_alias(Toggleable_Trait::class, \Sylius\Component\Resource\Model\Toggleable_Trait::class);
}