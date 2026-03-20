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
namespace Sylius\Component\Resource\Exception;

class_exists(\Sylius\Resource\Exception\Variant_With_No_Options_Values_Exception::class);
if (false) {
    trigger_deprecation('sylius/resource-bundle', '1.13', 'The "%s" class is deprecated and will be removed in 2.0.', Variant_With_No_Options_Values_Exception::class);
    /** @deprecated since SyliusResourceBundle 1.13 and will be removed in 2.0. */
    final class Variant_With_No_Options_Values_Exception extends \Sylius\Resource\Exception\Variant_With_No_Options_Values_Exception
    {
    }
}