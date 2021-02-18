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

use Darvin\Utils\Data\View\Factory\DataViewFactoryInterface;
use Darvin\Utils\Data\View\Renderer\DataViewRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Data view Twig extension
 */
class ViewExtension extends AbstractExtension
{
    /**
     * @var \Darvin\Utils\Data\View\Factory\DataViewFactoryInterface
     */
    private $factory;

    /**
     * @var \Darvin\Utils\Data\View\Renderer\DataViewRendererInterface
     */
    private $renderer;

    /**
     * @param \Darvin\Utils\Data\View\Factory\DataViewFactoryInterface   $factory  Data view factory
     * @param \Darvin\Utils\Data\View\Renderer\DataViewRendererInterface $renderer Data view renderer
     */
    public function __construct(DataViewFactoryInterface $factory, DataViewRendererInterface $renderer)
    {
        $this->factory = $factory;
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('utils_data_render', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string      $template    Template
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     * @param bool        $allowEmpty  Whether to allow empty view
     * @param array       $options     Options
     *
     * @return string
     */
    public function render(string $template, $data, ?string $name = null, ?string $transDomain = null, bool $allowEmpty = false, array $options = []): string
    {
        $view = $this->factory->createView($data, $name, $transDomain, $allowEmpty);

        if (null !== $view) {
            return $this->renderer->render($template, $view, $options);
        }

        return '';
    }
}
