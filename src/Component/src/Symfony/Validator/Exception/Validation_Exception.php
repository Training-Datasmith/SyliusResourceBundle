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
namespace Sylius\Resource\Symfony\Validator\Exception;

use Symfony\Component\Validator\Constraint_Violation_List_Interface;
/**
 * Thrown when a validation error occurs.
 *
 * @experimental
 */
final class Validation_Exception extends \RuntimeException implements Constraint_Violation_List_Aware_Exception_Interface
{
    public function __construct(private readonly Constraint_Violation_List_Interface $constraint_violation_list, string $message = '', int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message ?: $this->__toString(), $code, $previous);
    }
    public function get_constraint_violation_list(): Constraint_Violation_List_Interface
    {
        return $this->constraint_violation_list;
    }
    public function __toString(): string
    {
        $message = '';
        foreach ($this->constraint_violation_list as $violation) {
            if ('' !== $message) {
                $message .= "\n";
            }
            if ($property_path = $violation->get_property_path()) {
                $message .= "{$property_path}: ";
            }
            $message .= $violation->get_message();
        }
        return $message;
    }
}