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
namespace Sylius\Bundle\Resource_Bundle\Validator;

use Sylius\Bundle\Resource_Bundle\Validator\Constraints\Disabled;
use Sylius\Resource\Model\Toggleable_Interface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraint_Validator;
use Webmozart\Assert\Assert;
final class Disabled_Validator extends Constraint_Validator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::is_instance_of($constraint, Disabled::class);
        if (null === $value) {
            return;
        }
        if (!$value instanceof Toggleable_Interface) {
            throw new \InvalidArgumentException(sprintf('"%s" validates "%s" instances only', self::class, Toggleable_Interface::class));
        }
        if ($value->is_enabled()) {
            $this->context->add_violation($constraint->message);
        }
    }
}