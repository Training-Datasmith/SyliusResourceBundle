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

class Delete_Handling_Exception extends RuntimeException
{
    public function __construct(string $message = 'Ups, something went wrong during deleting a resource, please try again.', protected string $flash = 'something_went_wrong_error', protected int $api_response_code = 500, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    public function get_flash(): string
    {
        return $this->flash;
    }
    public function get_api_response_code(): int
    {
        return $this->api_response_code;
    }
}
if (!class_exists(\Sylius\Component\Resource\Exception\Delete_Handling_Exception::class, false)) {
    class_alias(Delete_Handling_Exception::class, \Sylius\Component\Resource\Exception\Delete_Handling_Exception::class);
}