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
    private const TEMPLATES = [
        DataViewRendererInterface::TEMPLATE_BLOCK => '@DarvinUtils/data/view/block.html.twig',
        DataViewRendererInterface::TEMPLATE_TABLE => '@DarvinUtils/data/view/table.html.twig',
        DataViewRendererInterface::TEMPLATE_TEXT  => '@DarvinUtils/data/view/text.txt.twig',
    ];

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
    public function render(string $template, DataView $view, array $options = []): string
    {
        return $this->twig->render(self::TEMPLATES[$template] ?? $template, [
            'view'    => $view,
            'options' => $options,
        ]);
    }
}
