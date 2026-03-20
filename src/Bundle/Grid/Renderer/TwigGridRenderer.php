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
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\Definition\Filter;
use Sylius\Component\Grid\Renderer\Grid_Renderer_Interface;
use Sylius\Component\Grid\View\Grid_View_Interface;
use Twig\Environment;
final class Twig_Grid_Renderer implements Grid_Renderer_Interface
{
    private readonly Grid_Renderer_Interface $grid_renderer;
    private readonly Environment $twig;
    public function __construct(Grid_Renderer_Interface $grid_renderer, Environment $twig, private readonly Options_Parser_Interface $options_parser, private array $action_templates = [])
    {
        $this->grid_renderer = $grid_renderer;
        $this->twig = $twig;
    }
    public function render(Grid_View_Interface $grid_view, ?string $template = null): string
    {
        return $this->grid_renderer->render($grid_view, $template);
    }
    /**
     * @param mixed $data
     */
    public function render_field(Grid_View_Interface $grid_view, Field $field, $data): string
    {
        return $this->grid_renderer->render_field($grid_view, $field, $data);
    }
    /**
     * @param mixed $data
     */
    public function render_action(Grid_View_Interface $grid_view, Action $action, $data = null): string
    {
        if (!$grid_view instanceof Resource_Grid_View) {
            return $this->grid_renderer->render_action($grid_view, $action, $data);
        }
        $type = $action->get_type();
        $template = method_exists($action, 'getTemplate') ? $action->get_template() : null;
        $template ??= $this->action_templates[$type] ?? null;
        if (null === $template) {
            throw new \InvalidArgumentException(sprintf('Missing template for action type "%s".', $type));
        }
        $options = $this->options_parser->parse_options($action->get_options(), $grid_view->get_request_configuration()->get_request(), $data);
        return $this->twig->render($template, ['grid' => $grid_view, 'action' => $action, 'data' => $data, 'options' => $options]);
    }
    public function render_filter(Grid_View_Interface $grid_view, Filter $filter): string
    {
        return $this->grid_renderer->render_filter($grid_view, $filter);
    }
}