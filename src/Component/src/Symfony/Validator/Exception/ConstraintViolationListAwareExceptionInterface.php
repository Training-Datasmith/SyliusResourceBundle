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
 * An exception which has a constraint violation list.
 *
 * @experimental
 */
interface Constraint_Violation_List_Aware_Exception_Interface
{
    /**
     * Gets constraint violations related to this exception.
     */
    public function get_constraint_violation_list(): Constraint_Violation_List_Interface;
}