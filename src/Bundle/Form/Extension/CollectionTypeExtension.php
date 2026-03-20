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
namespace Sylius\Bundle\Resource_Bundle\Form\Extension;

use Symfony\Component\Form\Abstract_Type_Extension;
use Symfony\Component\Form\Extension\Core\Type\Collection_Type;
use Symfony\Component\Form\Form_Interface;
use Symfony\Component\Form\Form_View;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Collection_Type_Extension extends Abstract_Type_Extension
{
    /**
     * @psalm-suppress MissingPropertyType
     */
    public function build_view(Form_View $view, Form_Interface $form, array $options): void
    {
        $view->vars['button_add_label'] = $options['button_add_label'];
        $view->vars['button_delete_label'] = $options['button_delete_label'];
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['button_add_label' => 'sylius.form.collection.add', 'button_delete_label' => 'sylius.form.collection.delete']);
    }
    public function get_extended_type(): string
    {
        return Collection_Type::class;
    }
    public static function get_extended_types(): iterable
    {
        return [Collection_Type::class];
    }
}