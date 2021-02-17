<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Twig\Extension\Data\View;

use Darvin\Utils\Data\View\Renderer\DataViewRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Data view renderer Twig extension
 */
class RendererExtension extends AbstractExtension
{
    /**
     * @var \Darvin\Utils\Data\View\Renderer\DataViewRendererInterface
     */
    private $renderer;

    /**
     * @param \Darvin\Utils\Data\View\Renderer\DataViewRendererInterface $renderer Data view renderer
     */
    public function __construct(DataViewRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('utils_data_block', [$this->renderer, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('utils_data_table', [$this->renderer, 'renderTable'], ['is_safe' => ['html']]),
            new TwigFunction('utils_data_text', [$this->renderer, 'renderText']),
        ];
    }
}
