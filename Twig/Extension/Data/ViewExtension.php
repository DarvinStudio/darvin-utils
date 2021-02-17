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
        $options = [
            'needs_environment' => true,
            'is_safe'           => ['html'],
        ];

        return [
            new TwigFunction('utils_data_block', [$this, 'renderBlock'], $options),
            new TwigFunction('utils_data_table', [$this, 'renderTable'], $options),
            new TwigFunction('utils_data_text', [$this, 'renderText'], $options),
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
        return $twig->render('@DarvinUtils/data/view/block.html.twig', [
            'view' => $this->factory->createView($data, $name, $transDomain),
        ]);
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
        return $twig->render('@DarvinUtils/data/view/table.html.twig', [
            'view' => $this->factory->createView($data, $name, $transDomain),
        ]);
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
        return $twig->render('@DarvinUtils/data/view/text.txt.twig', [
            'view' => $this->factory->createView($data, $name, $transDomain),
        ]);
    }
}
