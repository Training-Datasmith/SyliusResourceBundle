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
namespace Sylius\Bundle\Resource_Bundle\Validator\Constraints;

use Sylius\Bundle\Resource_Bundle\Validator\Unique_Within_Collection_Constraint_Validator;
use Symfony\Component\Validator\Constraint;
#[\Attribute]
final class Unique_Within_Collection_Constraint extends Constraint
{
    public string $message = 'This code must be unique within this collection.';
    public string $attribute_path = 'code';
    public function validated_by(): string
    {
        return Unique_Within_Collection_Constraint_Validator::class;
    }
}