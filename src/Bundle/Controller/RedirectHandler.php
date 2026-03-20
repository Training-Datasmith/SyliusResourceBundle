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
use Sylius\Resource\Resource_Actions;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Routing\Exception\Route_Not_Found_Exception;
use Symfony\Component\Routing\Router_Interface;
final readonly class Redirect_Handler implements Redirect_Handler_Interface
{
    private Router_Interface $router;
    public function __construct(Router_Interface $router)
    {
        $this->router = $router;
    }
    public function redirect_to_resource(Request_Configuration $configuration, Resource_Interface $resource): Response
    {
        try {
            return $this->redirect_to_route($configuration, (string) $configuration->get_redirect_route(Resource_Actions::SHOW), $configuration->get_redirect_parameters($resource));
        } catch (Route_Not_Found_Exception) {
            return $this->redirect_to_route($configuration, (string) $configuration->get_redirect_route(Resource_Actions::INDEX), $configuration->get_redirect_parameters($resource));
        }
    }
    public function redirect_to_index(Request_Configuration $configuration, ?Resource_Interface $resource = null): Response
    {
        return $this->redirect_to_route($configuration, (string) $configuration->get_redirect_route('index'), $configuration->get_redirect_parameters($resource));
    }
    public function redirect_to_route(Request_Configuration $configuration, string $route, array $parameters = []): Response
    {
        if ('referer' === $route) {
            return $this->redirect_to_referer($configuration);
        }
        return $this->redirect($configuration, $this->router->generate($route, $parameters));
    }
    public function redirect(Request_Configuration $configuration, string $url, int $status = 302): Response
    {
        if ($configuration->is_header_redirection()) {
            return new Response('', 200, ['X-SYLIUS-LOCATION' => $url . $configuration->get_redirect_hash()]);
        }
        return new Redirect_Response($url . $configuration->get_redirect_hash(), $status);
    }
    public function redirect_to_referer(Request_Configuration $configuration): Response
    {
        return $this->redirect($configuration, (string) $configuration->get_redirect_referer());
    }
}