<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Twig\Extension;

use Darvin\Utils\ObjectNamer\ObjectNamerInterface;

/**
 * Object namer Twig extension
 */
class ObjectNamerExtension extends \Twig_Extension
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
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('utils_name_object', [$this->objectNamer, 'name']),
        ];
    }
}
