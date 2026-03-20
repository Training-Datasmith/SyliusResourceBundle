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
use Sylius\Resource\Translation\Provider\Translation_Locale_Provider_Interface;
final readonly class Translatable_Entity_Locale_Assigner implements Translatable_Entity_Locale_Assigner_Interface
{
    public function __construct(private Translation_Locale_Provider_Interface $translation_locale_provider)
    {
    }
    public function assign_locale(Translatable_Interface $translatable_entity): void
    {
        $locale_code = $this->translation_locale_provider->get_default_locale_code();
        $translatable_entity->set_current_locale($locale_code);
        $translatable_entity->set_fallback_locale($locale_code);
    }
}
if (!class_exists(\Sylius\Component\Resource\Translation\Translatable_Entity_Locale_Assigner::class, false)) {
    class_alias(Translatable_Entity_Locale_Assigner::class, \Sylius\Component\Resource\Translation\Translatable_Entity_Locale_Assigner::class);
}