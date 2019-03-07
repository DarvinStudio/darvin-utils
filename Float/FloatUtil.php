<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Float;

/**
 * Float number utility
 */
final class FloatUtil
{
    private const EPSILON = 0.000001;

    /**
     * @param mixed $a First number
     * @param mixed $b Second number
     *
     * @return bool
     */
    public static function areEqual($a, $b): bool
    {
        $a = (float)$a;
        $b = (float)$b;

        return abs($a - $b) < self::EPSILON;
    }
}
