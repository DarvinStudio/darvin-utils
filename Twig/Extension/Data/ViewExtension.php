<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Twig\Extension\Data;

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
            new TwigFunction('utils_data_block', [$this, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('utils_data_table', [$this, 'renderTable'], ['is_safe' => ['html']]),
            new TwigFunction('utils_data_text', [$this, 'renderText']),
        ];
    }

    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     *
     * @return string
     */
    public function renderBlock($data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->renderer->renderBlock($this->factory->createView($data, $name, $transDomain));
    }

    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     *
     * @return string
     */
    public function renderTable($data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->renderer->renderTable($this->factory->createView($data, $name, $transDomain));
    }

    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     *
     * @return string
     */
    public function renderText($data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->renderer->renderText($this->factory->createView($data, $name, $transDomain));
    }
}
