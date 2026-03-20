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
namespace Sylius\Bundle\Resource_Bundle\Grid\Renderer;

use Sylius\Bundle\Resource_Bundle\Grid\Parser\Options_Parser_Interface;
use Sylius\Bundle\Resource_Bundle\Grid\View\Resource_Grid_View;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Renderer\Bulk_Action_Grid_Renderer_Interface;
use Sylius\Component\Grid\View\Grid_View_Interface;
use Twig\Environment;
use Webmozart\Assert\Assert;
final class Twig_Bulk_Action_Grid_Renderer implements Bulk_Action_Grid_Renderer_Interface
{
    private readonly Environment $twig;
    public function __construct(Environment $twig, private readonly Options_Parser_Interface $options_parser, private array $bulk_action_templates = [])
    {
        $this->twig = $twig;
    }
    public function render_bulk_action(Grid_View_Interface $grid_view, Action $bulk_action, $data = null): string
    {
        Assert::is_instance_of($grid_view, Resource_Grid_View::class);
        $type = $bulk_action->get_type();
        if (!isset($this->bulk_action_templates[$type])) {
            throw new \InvalidArgumentException(sprintf('Missing template for bulk action type "%s".', $type));
        }
        $options = $this->options_parser->parse_options($bulk_action->get_options(), $grid_view->get_request_configuration()->get_request(), $data);
        return $this->twig->render($this->bulk_action_templates[$type], ['grid' => $grid_view, 'action' => $bulk_action, 'data' => $data, 'options' => $options]);
    }
}