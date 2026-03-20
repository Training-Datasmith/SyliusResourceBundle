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
namespace Sylius\Bundle\Resource_Bundle\Controller;

use Symfony\Component\Http_Foundation\Request;
/**
 * Copied from symfony/http-request 7.4.
 * It's only used as bc-layer for the ParametersParser.
 *
 * @internal
 */
trait Bc_Layer_Request_Trait
{
    public function get_from_request(Request $request, string $key, mixed $default = null): mixed
    {
        if ($request->attributes->has($key)) {
            return $request->attributes->get($key, $default);
        }
        if ($request->query->has($key)) {
            return $request->query->all()[$key];
        }
        if ($request->request->has($key)) {
            return $request->request->all()[$key];
        }
        return $default;
    }
}