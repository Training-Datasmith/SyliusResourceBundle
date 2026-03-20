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
namespace Sylius\Resource\Model;

use Gedmo\Loggable\Entity\Mapped_Superclass\Abstract_Log_Entry;
use Sylius\Resource\Exception\RuntimeException;
if (!class_exists(Abstract_Log_Entry::class)) {
    throw new RuntimeException(sprintf('Cannot use the "%s" class when the "gedmo/doctrine-extensions" package is not installed.', Resource_Log_Entry::class));
}
abstract class Resource_Log_Entry extends Abstract_Log_Entry implements Resource_Interface
{
}
if (!class_exists(\Sylius\Component\Resource\Model\Resource_Log_Entry::class, false)) {
    class_alias(Resource_Log_Entry::class, \Sylius\Component\Resource\Model\Resource_Log_Entry::class);
}