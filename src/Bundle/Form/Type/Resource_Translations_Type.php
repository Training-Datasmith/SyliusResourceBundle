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
namespace Sylius\Bundle\Resource_Bundle\Form\Type;

use Sylius\Resource\Model\Translatable_Interface;
use Sylius\Resource\Model\Translation_Interface;
use Sylius\Resource\Translation\Provider\Translation_Locale_Provider_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Symfony\Component\Options_Resolver\Options_Resolver;
use Webmozart\Assert\Assert;
final class Resource_Translations_Type extends Abstract_Type
{
    /** @var string[] */
    private readonly array $defined_locales_codes;
    private readonly string $default_locale_code;
    public function __construct(Translation_Locale_Provider_Interface $locale_provider)
    {
        $this->defined_locales_codes = $locale_provider->get_defined_locales_codes();
        $this->default_locale_code = $locale_provider->get_default_locale_code();
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add_event_listener(Form_Events::SUBMIT, function (Form_Event $event): void {
            /** @var TranslationInterface[]|null[] $translations */
            $translations = $event->get_data();
            $parent_form = $event->get_form()->get_parent();
            Assert::not_null($parent_form);
            /** @var TranslatableInterface $translatable */
            $translatable = $parent_form->get_data();
            foreach ($translations as $locale_code => $translation) {
                if (null === $translation) {
                    unset($translations[$locale_code]);
                    continue;
                }
                $translation->set_locale($locale_code);
                $translation->set_translatable($translatable);
            }
            $event->set_data($translations);
        });
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['entries' => $this->defined_locales_codes, 'entry_name' => fn(string $locale_code): string => $locale_code, 'entry_options' => fn(string $locale_code): array => ['required' => $locale_code === $this->default_locale_code]]);
    }
    public function get_parent(): string
    {
        return Fixed_Collection_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_translations';
    }
}