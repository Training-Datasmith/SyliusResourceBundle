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

use Doctrine\Common\Collections\Collection;
interface Translatable_Interface
{
    /**
     * @return Collection|TranslationInterface[]
     * @psalm-return Collection<array-key, TranslationInterface>
     */
    public function get_translations(): Collection;
    public function get_translation(?string $locale = null): Translation_Interface;
    public function has_translation(Translation_Interface $translation): bool;
    public function add_translation(Translation_Interface $translation): void;
    public function remove_translation(Translation_Interface $translation): void;
    public function set_current_locale(string $locale): void;
    public function set_fallback_locale(string $locale): void;
}
if (!class_exists(\Sylius\Component\Resource\Model\Translatable_Interface::class, false)) {
    class_alias(Translatable_Interface::class, \Sylius\Component\Resource\Model\Translatable_Interface::class);
}