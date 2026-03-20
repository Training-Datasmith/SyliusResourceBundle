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
namespace Sylius\Bundle\Resource_Bundle\Form\Builder;

use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Form\Form_Builder_Interface;
interface Default_Form_Builder_Interface
{
    public function build(Metadata_Interface $metadata, Form_Builder_Interface $form_builder, array $options): void;
}