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

use Darvin\Utils\Data\View\Factory\DataViewFactoryInterface;
use Twig\Environment;

/**
 * Data view renderer
 */
class DataViewRenderer implements DataViewRendererInterface
{
    /**
     * @var \Darvin\Utils\Data\View\Factory\DataViewFactoryInterface
     */
    private $factory;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\Utils\Data\View\Factory\DataViewFactoryInterface $factory Data view factory
     * @param \Twig\Environment                                        $twig    Twig
     */
    public function __construct(DataViewFactoryInterface $factory, Environment $twig)
    {
        $this->factory = $factory;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderBlock($data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->render($data, $name, $transDomain, '@DarvinUtils/data/view/block.html.twig');
    }

    /**
     * {@inheritDoc}
     */
    public function renderTable($data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->render($data, $name, $transDomain, '@DarvinUtils/data/view/table.html.twig');
    }

    /**
     * {@inheritDoc}
     */
    public function renderText($data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->render($data, $name, $transDomain, '@DarvinUtils/data/view/text.txt.twig');
    }

    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     * @param string      $template    Template
     *
     * @return string
     */
    private function render($data, ?string $name, ?string $transDomain, string $template): string
    {
        return $this->twig->render($template, [
            'view' => $this->factory->createView($data, $name, $transDomain),
        ]);
    }
}
