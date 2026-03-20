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
namespace Sylius\Resource\Symfony\Routing;

use Sylius\Resource\Metadata\Http_Operation;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
/**
 * @experimental
 */
interface Redirect_Handler_Interface
{
    public const REFERER = 'referer';
    public function redirect_to_resource(mixed $data, Http_Operation $operation, Request $request): Redirect_Response;
    public function redirect_to_operation(mixed $data, Http_Operation $operation, Request $request, string $new_operation): Redirect_Response;
    public function redirect_to_route(mixed $data, string $route, array $parameters = []): Redirect_Response;
}