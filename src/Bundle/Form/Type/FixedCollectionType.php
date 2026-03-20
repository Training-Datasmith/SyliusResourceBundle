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

use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Options_Resolver\Options;
use Symfony\Component\Options_Resolver\Options_Resolver;
use Webmozart\Assert\Assert;
final class Fixed_Collection_Type extends Abstract_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        Assert::is_iterable($options['entries']);
        foreach ($options['entries'] as $entry) {
            Assert::is_callable($options['entry_type']);
            Assert::is_callable($options['entry_name']);
            Assert::is_callable($options['entry_options']);
            $entry_type = $options['entry_type']($entry);
            $entry_name = $options['entry_name']($entry);
            $entry_options = $options['entry_options']($entry);
            $builder->add($entry_name, $entry_type, array_replace(['property_path' => '[' . $entry_name . ']', 'block_name' => 'entry'], $entry_options));
        }
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_required('entries');
        $resolver->set_allowed_types('entries', ['array', \Traversable::class]);
        $resolver->set_required('entry_type');
        $resolver->set_allowed_types('entry_type', ['string', 'callable']);
        $resolver->set_normalizer('entry_type', $this->optional_callable_normalizer());
        $resolver->set_required('entry_name');
        $resolver->set_allowed_types('entry_name', ['callable']);
        $resolver->set_default('entry_options', fn() => []);
        $resolver->set_allowed_types('entry_options', ['array', 'callable']);
        $resolver->set_normalizer('entry_options', $this->optional_callable_normalizer());
    }
    public function get_block_prefix(): string
    {
        return 'sylius_fixed_collection';
    }
    private function optional_callable_normalizer(): \Closure
    {
        return function (Options $options, $value): callable|\Closure {
            if (is_callable($value)) {
                return $value;
            }
            return fn() => $value;
        };
    }
}