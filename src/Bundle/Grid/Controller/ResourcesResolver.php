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
namespace Sylius\Bundle\Resource_Bundle\Grid\Controller;

use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration;
use Sylius\Bundle\Resource_Bundle\Controller\Resources_Resolver_Interface;
use Sylius\Bundle\Resource_Bundle\Grid\View\Resource_Grid_View_Factory_Interface;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\Grid_Provider_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
final readonly class Resources_Resolver implements Resources_Resolver_Interface
{
    private Grid_Provider_Interface $grid_provider;
    public function __construct(private Resources_Resolver_Interface $decorated_resolver, Grid_Provider_Interface $grid_provider, private Resource_Grid_View_Factory_Interface $grid_view_factory)
    {
        $this->grid_provider = $grid_provider;
    }
    /**
     * @psalm-suppress MissingReturnType
     */
    public function get_resources(Request_Configuration $request_configuration, Repository_Interface $repository)
    {
        if (!$request_configuration->has_grid()) {
            return $this->decorated_resolver->get_resources($request_configuration, $repository);
        }
        $grid_definition = $this->grid_provider->get($request_configuration->get_grid());
        $request = $request_configuration->get_request();
        $parameters = new Parameters($request->query->all());
        $grid_view = $this->grid_view_factory->create($grid_definition, $parameters, $request_configuration->get_metadata(), $request_configuration);
        if ($request_configuration->is_html_request()) {
            return $grid_view;
        }
        return $grid_view->get_data();
    }
}