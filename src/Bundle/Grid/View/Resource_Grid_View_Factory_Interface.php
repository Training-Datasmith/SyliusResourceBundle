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
use Sylius\Resource\Metadata\Metadata_Interface;
interface Resource_Grid_View_Factory_Interface
{
    public function create(Grid $grid, Parameters $parameters, Metadata_Interface $metadata, Request_Configuration $request_configuration): Resource_Grid_View;
}