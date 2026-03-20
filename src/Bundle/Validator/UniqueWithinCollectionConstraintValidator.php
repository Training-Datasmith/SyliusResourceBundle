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

use Sylius\Bundle\Resource_Bundle\Validator\Constraints\Unique_Within_Collection_Constraint;
use Symfony\Component\Property_Access\Property_Access;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraint_Validator;
use Webmozart\Assert\Assert;
final class Unique_Within_Collection_Constraint_Validator extends Constraint_Validator
{
    /** @param iterable $value */
    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::is_instance_of($constraint, Unique_Within_Collection_Constraint::class);
        $property_accessor = Property_Access::create_property_accessor();
        $collection_of_entities_codes = [];
        foreach ($value as $key => $entity) {
            /** @var int|string|null $checkingAttribute */
            $checking_attribute = $property_accessor->get_value($entity, $constraint->attribute_path);
            if (null === $checking_attribute) {
                continue;
            }
            if (!array_key_exists($checking_attribute, $collection_of_entities_codes)) {
                $collection_of_entities_codes[$checking_attribute] = $key;
                continue;
            }
            $this->context->build_violation($constraint->message)->at_path(sprintf('[%d].%s', $key, $constraint->attribute_path))->add_violation();
            if (false !== $collection_of_entities_codes[$checking_attribute]) {
                $this->context->build_violation($constraint->message)->at_path(sprintf('[%d].%s', $collection_of_entities_codes[$checking_attribute], $constraint->attribute_path))->add_violation();
                $collection_of_entities_codes[$checking_attribute] = false;
            }
        }
    }
}