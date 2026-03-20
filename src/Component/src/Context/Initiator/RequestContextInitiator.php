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
namespace Sylius\Resource\Context\Initiator;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Symfony\Component\Http_Foundation\Request;
final class Request_Context_Initiator implements Request_Context_Initiator_Interface
{
    public function initialize_context(Request $request): Context
    {
        return new Context(new Request_Option($request));
    }
}