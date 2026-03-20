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

use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\Pagerfanta_Factory;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\Resource_Bundle\Grid\View\Resource_Grid_View;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
final readonly class Resources_Collection_Provider implements Resources_Collection_Provider_Interface
{
    public function __construct(private Resources_Resolver_Interface $resources_resolver, private ?Pagerfanta_Factory $pagerfanta_representation_factory = null)
    {
    }
    /**
     * @psalm-suppress MissingReturnType
     */
    public function get(Request_Configuration $request_configuration, Repository_Interface $repository)
    {
        $resources = $this->resources_resolver->get_resources($request_configuration, $repository);
        $pagination_limits = [];
        if ($resources instanceof Resource_Grid_View) {
            $paginator = $resources->get_data();
            $pagination_limits = $resources->get_definition()->get_limits();
        } else {
            $paginator = $resources;
        }
        if ($paginator instanceof Pagerfanta) {
            $request = $request_configuration->get_request();
            $paginator->set_max_per_page($this->resolve_max_per_page($request->query->has('limit') ? $request->query->get_int('limit') : null, $request_configuration->get_pagination_max_per_page(), $pagination_limits));
            $current_page = (int) $request->query->get('page', '1');
            $paginator->set_current_page($current_page);
            // This prevents Pagerfanta from querying database from a template
            $paginator->get_current_page_results();
            if (!$request_configuration->is_html_request()) {
                if (null === $this->pagerfanta_representation_factory) {
                    throw new \LogicException('The "willdurand/hateoas-bundle" must be installed and configured to render a resource collection on non-HTML request. Try running "composer require willdurand/hateoas-bundle"');
                }
                $route = new Route($request->attributes->get('_route'), array_merge($request->attributes->get('_route_params'), $request->query->all()));
                return $this->pagerfanta_representation_factory->create_representation($paginator, $route);
            }
        }
        return $resources;
    }
    /**
     * @param int[] $gridLimits
     */
    private function resolve_max_per_page(?int $request_limit, int $configuration_limit, array $grid_limits = []): int
    {
        if (null === $request_limit) {
            return reset($grid_limits) ?: $configuration_limit;
        }
        if (!empty($grid_limits)) {
            $max_grid_limit = max($grid_limits);
            return $request_limit > $max_grid_limit ? $max_grid_limit : $request_limit;
        }
        return $request_limit;
    }
}