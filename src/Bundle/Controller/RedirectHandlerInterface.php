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

use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Http_Foundation\Response;
interface Redirect_Handler_Interface
{
    public function redirect_to_resource(Request_Configuration $configuration, Resource_Interface $resource): Response;
    public function redirect_to_index(Request_Configuration $configuration, ?Resource_Interface $resource = null): Response;
    public function redirect_to_route(Request_Configuration $configuration, string $route, array $parameters = []): Response;
    public function redirect(Request_Configuration $configuration, string $url, int $status = 302): Response;
    public function redirect_to_referer(Request_Configuration $configuration): Response;
}