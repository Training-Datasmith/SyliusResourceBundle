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
namespace Sylius\Bundle\Resource_Bundle\Grid\View;

use Sylius\Bundle\Resource_Bundle\Controller\Parameters_Parser_Interface;
use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration;
use Sylius\Component\Grid\Data\Data_Provider_Interface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Resource\Metadata\Metadata_Interface;
final readonly class Resource_Grid_View_Factory implements Resource_Grid_View_Factory_Interface
{
    private Data_Provider_Interface $data_provider;
    public function __construct(Data_Provider_Interface $data_provider, private Parameters_Parser_Interface $parameters_parser)
    {
        $this->data_provider = $data_provider;
    }
    public function create(Grid $grid, Parameters $parameters, Metadata_Interface $metadata, Request_Configuration $request_configuration): Resource_Grid_View
    {
        $driver_configuration = $grid->get_driver_configuration();
        $request = $request_configuration->get_request();
        $grid->set_driver_configuration($this->parameters_parser->parse_request_values($driver_configuration, $request));
        return new Resource_Grid_View($this->data_provider->get_data($grid, $parameters), $grid, $parameters, $metadata, $request_configuration);
    }
}