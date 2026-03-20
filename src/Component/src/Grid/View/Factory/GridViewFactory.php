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
namespace Sylius\Resource\Grid\View\Factory;

use Sylius\Component\Grid\Data\Data_Provider_Interface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\View\Grid_View;
use Sylius\Resource\Context\Context;
final readonly class Grid_View_Factory implements Grid_View_Factory_Interface
{
    public function __construct(private Data_Provider_Interface $data_provider)
    {
    }
    public function create(Grid $grid, Context $context, Parameters $parameters, array $driver_configuration): Grid_View
    {
        return new Grid_View($this->data_provider->get_data($grid, $parameters), $grid, $parameters);
    }
}