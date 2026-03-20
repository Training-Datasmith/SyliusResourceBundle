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
namespace Sylius\Resource\Translation;

use Sylius\Resource\Model\Translatable_Interface;
interface Translatable_Entity_Locale_Assigner_Interface
{
    public function assign_locale(Translatable_Interface $translatable_entity): void;
}
if (!class_exists(\Sylius\Component\Resource\Translation\Translatable_Entity_Locale_Assigner_Interface::class, false)) {
    class_alias(Translatable_Entity_Locale_Assigner_Interface::class, \Sylius\Component\Resource\Translation\Translatable_Entity_Locale_Assigner_Interface::class);
}