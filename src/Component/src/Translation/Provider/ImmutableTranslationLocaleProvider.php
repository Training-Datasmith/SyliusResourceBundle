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

final readonly class Immutable_Translation_Locale_Provider implements Translation_Locale_Provider_Interface
{
    public function __construct(private array $defined_locales_codes, private string $default_locale_code)
    {
    }
    public function get_defined_locales_codes(): array
    {
        return $this->defined_locales_codes;
    }
    public function get_default_locale_code(): string
    {
        return $this->default_locale_code;
    }
}
if (!class_exists(\Sylius\Component\Resource\Translation\Provider\Immutable_Translation_Locale_Provider::class, false)) {
    class_alias(Immutable_Translation_Locale_Provider::class, \Sylius\Component\Resource\Translation\Provider\Immutable_Translation_Locale_Provider::class);
}