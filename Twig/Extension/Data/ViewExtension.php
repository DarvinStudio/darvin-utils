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
use Twig\Environment;
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
     * @param \Darvin\Utils\Data\View\Factory\DataViewFactoryInterface $factory Data view factory
     */
    public function __construct(DataViewFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('utils_data_block', [$this, 'renderBlock'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new TwigFunction('utils_data_table', [$this, 'renderTable'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new TwigFunction('utils_data_text', [$this, 'renderText'], [
                'needs_environment' => true,
            ]),
        ];
    }

    /**
     * @param \Twig\Environment $twig        Twig
     * @param mixed             $data        Data
     * @param string|null       $name        Name
     * @param string|null       $transDomain Translation domain
     *
     * @return string
     */
    public function renderBlock(Environment $twig, $data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->render($twig, $data, $name, $transDomain, '@DarvinUtils/data/view/block.html.twig');
    }

    /**
     * @param \Twig\Environment $twig        Twig
     * @param mixed             $data        Data
     * @param string|null       $name        Name
     * @param string|null       $transDomain Translation domain
     *
     * @return string
     */
    public function renderTable(Environment $twig, $data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->render($twig, $data, $name, $transDomain, '@DarvinUtils/data/view/table.html.twig');
    }

    /**
     * @param \Twig\Environment $twig        Twig
     * @param mixed             $data        Data
     * @param string|null       $name        Name
     * @param string|null       $transDomain Translation domain
     *
     * @return string
     */
    public function renderText(Environment $twig, $data, ?string $name = null, ?string $transDomain = null): string
    {
        return $this->render($twig, $data, $name, $transDomain, '@DarvinUtils/data/view/text.txt.twig');
    }

    /**
     * @param \Twig\Environment $twig        Twig
     * @param mixed             $data        Data
     * @param string|null       $name        Name
     * @param string|null       $transDomain Translation domain
     * @param string            $template    Template
     *
     * @return string
     */
    private function render(Environment $twig, $data, ?string $name, ?string $transDomain, string $template): string
    {
        return $twig->render($template, [
            'view' => $this->factory->createView($data, $name, $transDomain),
        ]);
    }
}
