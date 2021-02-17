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
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

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
    public function getFilters(): array
    {
        return [
            new TwigFilter('utils_data_to_view', [$this->factory, 'createView']),
        ];
    }
}
