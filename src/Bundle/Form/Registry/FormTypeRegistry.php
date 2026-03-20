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

final class Form_Type_Registry implements Form_Type_Registry_Interface
{
    private array $form_types = [];
    public function add(string $identifier, string $type_identifier, string $form_type): void
    {
        $this->form_types[$identifier][$type_identifier] = $form_type;
    }
    public function get(string $identifier, string $type_identifier): ?string
    {
        if (!$this->has($identifier, $type_identifier)) {
            return null;
        }
        return $this->form_types[$identifier][$type_identifier];
    }
    public function has(string $identifier, string $type_identifier): bool
    {
        return isset($this->form_types[$identifier][$type_identifier]);
    }
}