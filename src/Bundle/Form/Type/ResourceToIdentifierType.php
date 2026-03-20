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

use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Resource_To_Identifier_Transformer;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Bridge\Doctrine\Form\Type\Entity_Type;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Options_Resolver\Options_Resolver;
use Webmozart\Assert\Assert;
final class Resource_To_Identifier_Type extends Abstract_Type
{
    public function __construct(private readonly Repository_Interface $repository, private readonly Metadata_Interface $metadata)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $identifier = $options['identifier'];
        Assert::null_or_string($identifier);
        $builder->add_model_transformer(new Resource_To_Identifier_Transformer($this->repository, $identifier));
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['identifier' => 'id'])->set_allowed_types('identifier', 'string');
    }
    public function get_parent(): string
    {
        return Entity_Type::class;
    }
    public function get_block_prefix(): string
    {
        return sprintf('%s_%s_to_identifier', $this->metadata->get_application_name(), $this->metadata->get_name());
    }
}