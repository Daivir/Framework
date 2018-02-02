<?php
namespace App\Admin;

use App\Admin\AdminWidgetInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Framework\Renderer\RendererInterface;

class DashboardAction
{
    private $renderer;

    /**
     * @var AdminWidgetInterface[]
     */
    private $widgets;

    public function __construct(RendererInterface $renderer, array $widgets)
    {
        $this->renderer = $renderer;
        $this->widgets = $widgets;
    }

    public function __invoke(Request $request)
    {
        $widgets = array_reduce($this->widgets, function (string $html, AdminWidgetInterface $widget) {
            return $html . $widget->render();
        }, '');
        return $this->renderer->render('@admin/dashboard', compact('widgets'));
    }
}
