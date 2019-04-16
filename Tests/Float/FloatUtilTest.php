<?php
/**
 * @author    Maxim Sukhanov <syhanov.m@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Float;

use PHPUnit\Framework\TestCase;

/**
 * FloatUtil test
 * @group float
 */
class FloatUtilTest extends TestCase
{
    /**
     * @dataProvider dataProviderAreEqualPositive
     *
     * @param mixed $first
     * @param mixed $second
     */
    public function testAreEqualPositive($first, $second)
    {
        self::assertTrue(FloatUtil::areEqual($first, $second));
    }

    /**
     * @dataProvider dataProviderAreEqualNegative
     *
     * @param mixed $first
     * @param mixed $second
     */
    public function testAreEqualNegative($first, $second)
    {
        self::assertFalse(FloatUtil::areEqual($first, $second));
    }

    /**
     * @return array
     */
    public function dataProviderAreEqualPositive()
    {
        return [
            ['', ''],
            [null, null],
            [3, 3],
            ['3', '3'],
            ['3', 3],
            [3, '3'],
            ['first', 'second'],
            [0, 'second'],
            [2.1, 2.1],
            [2.1000001, 2.1000003],
            [true, 1],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderAreEqualNegative()
    {
        return [
            [2.100001, 2.100003],
            ['', 2.100003],
            [2.100003, ''],
            [NAN, NAN],
            [1, 3],
            ['1', 3],
            [1, '2'],
            [-11, 11],
        ];
    }
}
