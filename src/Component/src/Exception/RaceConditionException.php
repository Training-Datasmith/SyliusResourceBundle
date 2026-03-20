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
namespace Sylius\Resource\Exception;

class Race_Condition_Exception extends Update_Handling_Exception
{
    public function __construct(?\Exception $previous = null)
    {
        parent::__construct('Operated entity was previously modified.', 'race_condition_error', 409, null !== $previous ? (int) $previous->get_code() : 0, $previous);
    }
}
if (!class_exists(\Sylius\Component\Resource\Exception\Race_Condition_Exception::class, false)) {
    class_alias(Race_Condition_Exception::class, \Sylius\Component\Resource\Exception\Race_Condition_Exception::class);
}