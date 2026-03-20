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

use Webmozart\Assert\Assert;
class Abstract_Translation implements Translation_Interface
{
    protected ?string $locale = null;
    protected ?Translatable_Interface $translatable = null;
    public function get_translatable(): Translatable_Interface
    {
        $translatable = $this->translatable;
        // Return typehint should account for null value.
        Assert::not_null($translatable);
        return $translatable;
    }
    public function set_translatable(?Translatable_Interface $translatable): void
    {
        if ($translatable === $this->translatable) {
            return;
        }
        $previous_translatable = $this->translatable;
        $this->translatable = $translatable;
        if (null !== $previous_translatable) {
            $previous_translatable->remove_translation($this);
        }
        if (null !== $translatable) {
            $translatable->add_translation($this);
        }
    }
    public function get_locale(): ?string
    {
        return $this->locale;
    }
    public function set_locale(?string $locale): void
    {
        $this->locale = $locale;
    }
}
if (!class_exists(\Sylius\Component\Resource\Model\Abstract_Translation::class, false)) {
    class_alias(Abstract_Translation::class, \Sylius\Component\Resource\Model\Abstract_Translation::class);
}