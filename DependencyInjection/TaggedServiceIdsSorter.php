<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\DependencyInjection;

/**
 * Tagged service IDs sorter
 */
class TaggedServiceIdsSorter
{
    /**
     * @var string
     */
    private $positionArg;

    /**
     * @param string $positionArg Position argument name
     */
    public function __construct($positionArg = 'position')
    {
        $this->positionArg = $positionArg;
    }

    /**
     * @param array $ids Tagged service IDs
     */
    public function sort(array &$ids)
    {
        $defaultPos = $this->getMaxPosition($ids) + 1;
        $posArg     = $this->positionArg;

        uasort($ids, function (array $a, array $b) use ($defaultPos, $posArg) {
            $posA = (int)(isset($a[0][$posArg]) ? $a[0][$posArg] : $defaultPos);
            $posB = (int)(isset($b[0][$posArg]) ? $b[0][$posArg] : $defaultPos);

            return $posA === $posB ? 0 : ($posA > $posB ? 1 : -1);
        });
    }

    /**
     * @param array $ids Tagged service IDs
     *
     * @return int
     */
    private function getMaxPosition(array $ids)
    {
        $positions = [];

        foreach ($ids as $attr) {
            if (isset($attr[0][$this->positionArg])) {
                $positions[] = (int)$attr[0][$this->positionArg];
            }
        }

        return !empty($positions) ? max($positions) : 0;
    }
}
