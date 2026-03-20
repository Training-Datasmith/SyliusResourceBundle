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
namespace Sylius\Bundle\Resource_Bundle\Context\Option;

use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration;
final readonly class Request_Configuration_Option
{
    public function __construct(private Request_Configuration $request_configuration)
    {
    }
    public function request_configuration(): Request_Configuration
    {
        return $this->request_configuration;
    }
}