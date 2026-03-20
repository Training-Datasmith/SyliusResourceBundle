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

use Sylius\Bundle\Resource_Bundle\Controller\Request_Configuration;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\View\Grid_View;
use Sylius\Resource\Metadata\Metadata_Interface;
class Resource_Grid_View extends Grid_View
{
    /**
     * @param mixed $data
     */
    public function __construct($data, Grid $grid_definition, Parameters $parameters, private readonly Metadata_Interface $metadata, private readonly Request_Configuration $request_configuration)
    {
        parent::__construct($data, $grid_definition, $parameters);
    }
    public function get_metadata(): Metadata_Interface
    {
        return $this->metadata;
    }
    public function get_request_configuration(): Request_Configuration
    {
        return $this->request_configuration;
    }
}