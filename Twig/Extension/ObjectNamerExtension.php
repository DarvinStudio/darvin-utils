<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Twig\Extension;

use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Object namer Twig extension
 */
class ObjectNamerExtension extends AbstractExtension
{
    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    private $objectNamer;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     */
    public function __construct(ObjectNamerInterface $objectNamer)
    {
        $this->objectNamer = $objectNamer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('utils_name_object', [$this->objectNamer, 'name']),
        ];
    }
}
