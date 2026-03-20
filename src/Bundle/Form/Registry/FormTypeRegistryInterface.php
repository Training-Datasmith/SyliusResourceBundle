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
namespace Sylius\Bundle\Resource_Bundle\Form\Registry;

interface Form_Type_Registry_Interface
{
    public function add(string $identifier, string $type_identifier, string $form_type): void;
    public function get(string $identifier, string $type_identifier): ?string;
    public function has(string $identifier, string $type_identifier): bool;
}