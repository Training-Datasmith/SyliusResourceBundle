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

use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Collection_To_String_Transformer;
use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Recursive_Transformer;
use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Resource_To_Identifier_Transformer;
use Sylius\Component\Registry\Service_Registry_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Hidden_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Interface;
use Symfony\Component\Form\Form_View;
use Symfony\Component\Options_Resolver\Options;
use Symfony\Component\Options_Resolver\Options_Resolver;
use Webmozart\Assert\Assert;
class Resource_Autocomplete_Choice_Type extends Abstract_Type
{
    protected Service_Registry_Interface $resource_repository_registry;
    public function __construct(Service_Registry_Interface $resource_repository_registry)
    {
        $this->resource_repository_registry = $resource_repository_registry;
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        Assert::is_instance_of($options['repository'], Repository_Interface::class);
        Assert::null_or_string($options['choice_value']);
        if (!$options['multiple']) {
            $builder->add_model_transformer(new Resource_To_Identifier_Transformer($options['repository'], $options['choice_value']));
        }
        if ($options['multiple']) {
            $builder->add_model_transformer(new Recursive_Transformer(new Resource_To_Identifier_Transformer($options['repository'], $options['choice_value'])))->add_view_transformer(new Collection_To_String_Transformer(','));
        }
    }
    /**
     * @psalm-suppress MissingPropertyType
     */
    public function build_view(Form_View $view, Form_Interface $form, array $options): void
    {
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['choice_name'] = $options['choice_name'];
        $view->vars['choice_value'] = $options['choice_value'];
        $view->vars['placeholder'] = $options['placeholder'];
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_required(['resource', 'choice_name', 'choice_value'])->set_defaults(['multiple' => false, 'error_bubbling' => false, 'placeholder' => '', 'repository' => function (Options $options) {
            Assert::string($options['resource']);
            return $this->resource_repository_registry->get($options['resource']);
        }])->set_allowed_types('resource', ['string'])->set_allowed_types('multiple', ['bool'])->set_allowed_types('choice_name', ['string'])->set_allowed_types('choice_value', ['string'])->set_allowed_types('placeholder', ['string']);
    }
    public function get_parent(): string
    {
        return Hidden_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_resource_autocomplete_choice';
    }
}