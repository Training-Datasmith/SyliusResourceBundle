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
namespace Sylius\Resource\Translation\Provider;

interface Translation_Locale_Provider_Interface
{
    /** @return string[] */
    public function get_defined_locales_codes(): array;
    public function get_default_locale_code(): string;
}
if (!class_exists(\Sylius\Component\Resource\Translation\Provider\Translation_Locale_Provider_Interface::class, false)) {
    class_alias(Translation_Locale_Provider_Interface::class, \Sylius\Component\Resource\Translation\Provider\Translation_Locale_Provider_Interface::class);
}