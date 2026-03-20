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

class Delete_Resource_Exception extends Write_Resource_Exception
{
    public function __construct(?string $resource_name = null, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if (empty($message)) {
            $message = sprintf('Cannot delete, the %s is in use.', $resource_name ?? 'resource');
        }
        parent::__construct($resource_name, $message, $code, $previous);
    }
}