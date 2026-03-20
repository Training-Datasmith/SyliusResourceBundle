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

use Sylius\Bundle\Resource_Bundle\Context\Option\Request_Configuration_Option;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\View\Grid_View;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Metadata_Option;
use Sylius\Resource\Grid\View\Factory\Grid_View_Factory_Interface;
final readonly class Legacy_Grid_View_Factory implements Grid_View_Factory_Interface
{
    public function __construct(private Resource_Grid_View_Factory_Interface $resource_grid_view_factory, private Grid_View_Factory_Interface $decorated)
    {
    }
    public function create(Grid $grid, Context $context, Parameters $parameters, array $driver_configuration): Resource_Grid_View|Grid_View
    {
        $request_configuration = $context->get(Request_Configuration_Option::class)?->request_configuration();
        $metadata = $context->get(Metadata_Option::class)?->metadata();
        if (null === $request_configuration || null === $metadata) {
            return $this->decorated->create($grid, $context, $parameters, $driver_configuration);
        }
        return $this->resource_grid_view_factory->create($grid, $parameters, $metadata, $request_configuration);
    }
}