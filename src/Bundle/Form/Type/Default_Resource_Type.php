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

use Sylius\Bundle\Resource_Bundle\Form\Builder\Default_Form_Builder_Interface;
use Sylius\Component\Registry\Service_Registry_Interface;
use Sylius\Resource\Metadata\Registry_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Webmozart\Assert\Assert;
final class Default_Resource_Type extends Abstract_Type
{
    private readonly Service_Registry_Interface $form_builder_registry;
    public function __construct(private readonly Registry_Interface $metadata_registry, Service_Registry_Interface $form_builder_registry)
    {
        $this->form_builder_registry = $form_builder_registry;
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        Assert::string($options['data_class']);
        $metadata = $this->metadata_registry->get_by_class($options['data_class']);
        $driver = $metadata->get_driver();
        Assert::not_false($driver, sprintf('Form "%s" cannot be used with no driver configured on the resource "%s". Please define a form.', self::class, $metadata->get_alias()));
        /** @var DefaultFormBuilderInterface $formBuilder */
        $form_builder = $this->form_builder_registry->get($driver);
        $form_builder->build($metadata, $builder, $options);
    }
    public function get_block_prefix(): string
    {
        return 'sylius_resource';
    }
}