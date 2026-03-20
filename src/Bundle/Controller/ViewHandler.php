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

use FOS\Rest_Bundle\View\Configurable_View_Handler_Interface;
use FOS\Rest_Bundle\View\View;
use Symfony\Component\Http_Foundation\Response;
final readonly class View_Handler implements View_Handler_Interface
{
    public function __construct(private Configurable_View_Handler_Interface $rest_view_handler)
    {
    }
    public function handle(Request_Configuration $request_configuration, View $view): Response
    {
        if (!$request_configuration->is_html_request()) {
            $this->rest_view_handler->set_exclusion_strategy_groups($request_configuration->get_serialization_groups() ?? []);
            $version = $request_configuration->get_serialization_version();
            if (null !== $version) {
                $this->rest_view_handler->set_exclusion_strategy_version($version);
            }
            $view->get_context()->enable_max_depth();
        }
        return $this->rest_view_handler->handle($view);
    }
}