<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Data\View\Renderer;

use Darvin\Utils\Data\View\Model\DataView;
use Twig\Environment;

/**
 * Data view renderer
 */
class DataViewRenderer implements DataViewRendererInterface
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Twig\Environment $twig Twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderBlock(DataView $view, array $options = []): string
    {
        return $this->render($view, $options, '@DarvinUtils/data/view/block.html.twig');
    }

    /**
     * {@inheritDoc}
     */
    public function renderTable(DataView $view, array $options = []): string
    {
        return $this->render($view, $options, '@DarvinUtils/data/view/table.html.twig');
    }

    /**
     * {@inheritDoc}
     */
    public function renderText(DataView $view, array $options = []): string
    {
        return $this->render($view, $options, '@DarvinUtils/data/view/text.txt.twig');
    }

    /**
     * @param \Darvin\Utils\Data\View\Model\DataView $view     View
     * @param array                                  $options  Options
     * @param string                                 $template Template
     *
     * @return string
     */
    private function render(DataView $view, array $options, string $template): string
    {
        return $this->twig->render($template, [
            'options' => $options,
            'view'    => $view,
        ]);
    }
}
