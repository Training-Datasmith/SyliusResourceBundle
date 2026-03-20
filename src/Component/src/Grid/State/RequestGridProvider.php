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
namespace Sylius\Resource\Grid\State;

use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\Grid_Provider_Interface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Grid\View\Factory\Grid_View_Factory_Interface;
use Sylius\Resource\Metadata\Grid_Aware_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Provider_Interface;
final readonly class Request_Grid_Provider implements Provider_Interface
{
    private const DEFAULT_MAX_PER_PAGE = 10;
    public function __construct(private ?Grid_View_Factory_Interface $grid_view_factory = null, private ?Grid_Provider_Interface $grid_provider = null)
    {
    }
    public function provide(Operation $operation, Context $context): ?object
    {
        if (null === $this->grid_view_factory || null === $this->grid_provider) {
            throw new \LogicException('You can not use a grid if Sylius Grid Bundle is not available. Try running "composer require sylius/grid-bundle".');
        }
        if (!$operation instanceof Grid_Aware_Operation_Interface) {
            throw new \LogicException(sprintf('You can not use a grid if your operation does not implement "%s".', Grid_Aware_Operation_Interface::class));
        }
        $grid = $operation->get_grid();
        if (null === $grid) {
            throw new \RuntimeException(sprintf('Operation has no grid, so you cannot use this provider for operation "%s"', $operation->get_name() ?? ''));
        }
        $request = $context->get(Request_Option::class)?->request();
        if (null === $request) {
            return null;
        }
        $grid_definition = $this->grid_provider->get($grid);
        $grid_configuration = $grid_definition->get_driver_configuration();
        $parameters = $request->query->all();
        $grid_view = $this->grid_view_factory->create($grid_definition, $context, new Parameters($parameters), $grid_configuration);
        $data = $grid_view->get_data();
        if ($data instanceof Pagerfanta) {
            $current_page = $request->query->get_int('page', 1);
            $data->set_current_page($current_page);
            $max_per_page = $this->resolve_max_per_page($request->query->has('limit') ? $request->query->get_int('limit') : null, $grid_definition->get_limits());
            $data->set_max_per_page($max_per_page);
        }
        return $grid_view;
    }
    private function resolve_max_per_page(?int $request_limit, array $grid_limits = []): int
    {
        if (null === $request_limit) {
            $first_grid_limit = reset($grid_limits);
            return false === $first_grid_limit ? self::DEFAULT_MAX_PER_PAGE : $first_grid_limit;
        }
        if (!empty($grid_limits)) {
            $max_grid_limit = max($grid_limits);
            // Cannot retrieve more items than configured in the grid
            return min($request_limit, $max_grid_limit);
        }
        return $request_limit;
    }
}