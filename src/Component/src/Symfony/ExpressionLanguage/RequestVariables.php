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
namespace Sylius\Resource\Symfony\Expression_Language;

use Symfony\Component\Http_Foundation\Request_Stack;
/**
 * @experimental
 */
final readonly class Request_Variables implements Variables_Interface
{
    public function __construct(private Request_Stack $request_stack)
    {
    }
    public function get_variables(): array
    {
        return ['request' => $this->request_stack->get_current_request()];
    }
}