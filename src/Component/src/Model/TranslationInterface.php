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

interface Translation_Interface
{
    public function get_translatable(): Translatable_Interface;
    public function set_translatable(?Translatable_Interface $translatable): void;
    public function get_locale(): ?string;
    public function set_locale(?string $locale): void;
}
if (!class_exists(\Sylius\Component\Resource\Model\Translation_Interface::class, false)) {
    class_alias(Translation_Interface::class, \Sylius\Component\Resource\Model\Translation_Interface::class);
}