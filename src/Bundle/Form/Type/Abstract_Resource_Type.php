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
use Symfony\Component\Options_Resolver\Options_Resolver;
abstract class Abstract_Resource_Type extends Abstract_Type
{
    /**
     * @param string $dataClass FQCN
     * @param string[] $validationGroups
     */
    public function __construct(protected string $data_class, protected array $validation_groups = [])
    {
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['data_class' => $this->data_class, 'validation_groups' => $this->validation_groups]);
    }
}