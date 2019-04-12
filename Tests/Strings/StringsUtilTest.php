<?php
/**
 * @author    Maxim Sukhanov <syhanov.m@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings;


use PHPUnit\Framework\TestCase;

/**
 * StringsUtil test
 *
 * @group strings
 */
class StringsUtilTest extends TestCase
{

    public function testIsUppercase()
    {

        $positive = ['', 'TEST', 'ТЕСТ', 'ТЕСТ ТЕСТ', '!@#$%^&*', '123123123', ' ТЕСТ '];
        $negative = [null, 123456789, -1234567890, 123.3366, 'test', 'тест', 'Тест', NAN];

        foreach ($positive as $item) {

            self::assertTrue(StringsUtil::isUppercase($item));

        }

        foreach ($negative as $item) {

            self::assertFalse(StringsUtil::isUppercase($item));

        }

    }

    /**
     * @dataProvider dataProviderHumanize
     *
     * @param string\null $expected
     * @param mixed $input
     */
    public function testHumanize($expected, $input)
    {

        self::assertEquals($expected, StringsUtil::humanize($input));

    }

    /**
     * @dataProvider dataProviderLowercaseFirst
     *
     * @param string\null $expected
     * @param mixed $input
     */
    public function testLowercaseFirst($expected, $input)
    {

        self::assertEquals($expected, StringsUtil::lowercaseFirst($input));


    }

    /**
     * @dataProvider dataProviderToUnderscore
     *
     * @param string\null $expected
     * @param mixed $input
     */

    public function testToUnderscore($expected, $input)
    {

        self::assertEquals($expected,StringsUtil::toUnderscore($input));

    }

    /**
     * @dataProvider dataProviderToCamelCase
     *
     * @param string\null $expected
     * @param mixed $input
     */
    public function testToCamelCase($expected, $input)
    {

        self::assertEquals($expected,StringsUtil::toCamelCase($input));

    }

    /**
     * @return array
     */
    public function dataProviderHumanize()
    {

        return [
            [null, null],
            ['User type', 'user_type'],
            ['Controller', 'controller'],
            ['Test test', 'testTest'],
            ['Test test', 'TestTest'],
            ['Test', 'Test'],
        ];

    }

    /**
     * @return array
     */
    public function dataProviderLowercaseFirst()
    {

        return [
            [null, null],
            ['', ''],
            ['content', 'Content'],
            ['cONTENT', 'CONTENT'],
            ['123', '123'],
            ['test_Test', 'Test_Test'],
        ];

    }

    /**
     * @return array
     */
    public function dataProviderToUnderscore()
    {

        return [
            [null, null],
            ['', ''],
            ['test_test', 'testTest'],
            ['test_test_test', 'testTestTest'],
        ];

    }

    /**
     * @return array
     */
    public function dataProviderToCamelCase()
    {

        return [
            [null, null],
            ['', ''],
            ['TestTest', 'test_test'],
            ['TestTestTest', 'test_test_test'],
            ['TestTestTest', 'test_test_test'],
            ['TestTest', 'Test_Test'],
            ['123123', '123_123'],
        ];

    }

}
