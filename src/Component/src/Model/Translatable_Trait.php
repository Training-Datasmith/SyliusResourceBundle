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

use Doctrine\Common\Collections\Array_Collection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Persistent_Collection;
/**
 * @see TranslatableInterface
 */
trait Translatable_Trait
{
    /** @var ArrayCollection|PersistentCollection|TranslationInterface[] */
    protected $translations;
    /** @var array|TranslationInterface[] */
    protected $translations_cache = [];
    /** @var string|null */
    protected $current_locale;
    /**
     * Cache current translation. Useful in Doctrine 2.4+
     *
     * @var TranslationInterface|null
     */
    protected $current_translation;
    /** @var string|null */
    protected $fallback_locale;
    public function __construct()
    {
        $this->translations = new Array_Collection();
    }
    public function get_translation(?string $locale = null): Translation_Interface
    {
        $locale = $locale ?: $this->current_locale;
        if (null === $locale) {
            throw new \RuntimeException('No locale has been set and current locale is undefined.');
        }
        if (isset($this->translations_cache[$locale])) {
            return $this->translations_cache[$locale];
        }
        $translation = $this->translations->get($locale);
        if (null !== $translation) {
            $this->translations_cache[$locale] = $translation;
            return $translation;
        }
        if ($locale !== $this->fallback_locale) {
            if (isset($this->translations_cache[$this->fallback_locale])) {
                return $this->translations_cache[$this->fallback_locale];
            }
            $fallback_translation = $this->translations->get($this->fallback_locale);
            if (null !== $fallback_translation) {
                $this->translations_cache[$this->fallback_locale] = $fallback_translation;
                return $fallback_translation;
            }
        }
        $translation = $this->create_translation();
        $translation->set_locale($locale);
        $this->add_translation($translation);
        $this->translations_cache[$locale] = $translation;
        return $translation;
    }
    /**
     * @return Collection|TranslationInterface[]
     */
    public function get_translations(): Collection
    {
        return $this->translations;
    }
    public function has_translation(Translation_Interface $translation): bool
    {
        return isset($this->translations_cache[$translation->get_locale()]) || $this->translations->contains_key($translation->get_locale());
    }
    public function add_translation(Translation_Interface $translation): void
    {
        if (!$this->has_translation($translation)) {
            $this->translations_cache[$translation->get_locale()] = $translation;
            $this->translations->set($translation->get_locale(), $translation);
            $translation->set_translatable($this);
        }
    }
    public function remove_translation(Translation_Interface $translation): void
    {
        if ($this->translations->remove_element($translation)) {
            unset($this->translations_cache[$translation->get_locale()]);
            $translation->set_translatable(null);
        }
    }
    public function set_current_locale(string $current_locale): void
    {
        $this->current_locale = $current_locale;
    }
    public function set_fallback_locale(string $fallback_locale): void
    {
        $this->fallback_locale = $fallback_locale;
    }
    /**
     * Create resource translation model.
     */
    abstract protected function create_translation(): Translation_Interface;
}
if (!class_exists(\Sylius\Component\Resource\Model\Translatable_Trait::class, false)) {
    class_alias(Translatable_Trait::class, \Sylius\Component\Resource\Model\Translatable_Trait::class);
}