<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Iterable;

/**
 * Iterable utility
 */
final class IterableUtil
{
    /**
     * @param iterable $iterable Iterable
     *
     * @return array
     */
    public static function toArray($iterable)
    {
        return $iterable instanceof \Traversable ? iterator_to_array($iterable) : $iterable;
    }
}
