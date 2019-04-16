<?php
/**
 * @author    Maxim Sukhanov <syhanov.m@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Transliteratable;

use PHPUnit\Framework\TestCase;

/**
 * Transliterator test
 *
 * @group transliterator
 */
class TransliteratorTest extends TestCase
{
    /**
     * @var \Darvin\Utils\Transliteratable\Transliterator|null
     */
    private $transliterator;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->transliterator = new Transliterator();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->transliterator = null;
    }

    /**
     * @dataProvider dataProviderString
     *
     * @param string $expected
     * @param mixed  $input
     */
    public function testTransliterate($expected, $input)
    {
        self::assertEquals($expected, $this->transliterator->transliterate($input));
    }

    /**
     * @dataProvider dataProviderNoSanitize
     *
     * @param string $expected
     * @param mixed  $input
     */
    public function testTransliterateNoSanitize($expected, $input)
    {
        self::assertEquals($expected, $this->transliterator->transliterate($input, false));
    }

    /**
     * @dataProvider dataProviderAllowedSymbols
     *
     * @param string   $expected
     * @param mixed    $input
     * @param string[] $allowedSymbols
     */
    public function testTransliterateWithAllowedSymbols($expected, $input, array $allowedSymbols)
    {
        self::assertEquals($expected, $this->transliterator->transliterate($input, true, $allowedSymbols));
    }

    /**
     * @dataProvider dataProviderSeparator
     *
     * @param string $expected
     * @param mixed  $input
     * @param string $separator
     */
    public function testTransliterateWithSeparator($expected, $input, $separator)
    {
        self::assertEquals($expected, $this->transliterator->transliterate($input, true, [], $separator));
    }

    /**
     * @return array
     */
    public function dataProviderString()
    {
        return [
            'alphabet' => ['abvgdeyozhziyklmnoprstufhcchshshyeyuya', 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя'],
            'text' => ['test-test-test', 'тест тест тест'],
            'hyphen 1' => ['test', '  тест  '],
            'hyphen 2' => ['test-test', 'тест       тест'],
            'mixed' => ['test-no1', 'Тест №1!'],
            'special chars' => ['', '! ? , . \" \' | \\ : ; [ ] { } - = + ( ) # $ % ^ & * @ /'],
            'number' => ['1234567890', 1234567890],
            'negative' => ['2222', -2222],
            'float number' => ['123456-789', 123456.789],
            'null' => ['', null],
            'empty' => ['', ''],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderNoSanitize()
    {
        return [
            'text' => ['test test test', 'тест тест тест'],
            'hyphen 1' => ['  test  ', '  тест  '],
            'hyphen 2' => ['test  test', 'тест  тест'],
            'mixed' => ['test no1!', 'Тест №1!'],
            'special chars' => ['! ? , . \" \' | \\ : ; [ ] { } - = + ( ) # $ % ^ & * @ /', '! ? , . \" \' | \\ : ; [ ] { } - = + ( ) # $ % ^ & * @ /'],
            'number' => ['1234567890', 1234567890],
            'float number' => ['123456.789', 123456.789],
            'negative' => ['-2222', -2222],
            'null' => ['', null],
            'empty' => ['', ''],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderAllowedSymbols()
    {
        return [
            ['!', '!', ['!']],
            ['?', '?', ['?']],
            [',', ',', [',']],
            ['.', '.', ['.']],
            ['\'', '\'', ['\'']],
            ['|', '|', ['|']],
            ['\\', '\\', ['\\']],
            [':', ':', [':']],
            [';', ';', [';']],
            ['[', '[', ['[']],
            [']', ']', [']']],
            ['{', '{', ['{']],
            ['}', '}', ['}']],
            ['-', '-', ['-']],
            ['=', '=', ['=']],
            ['+', '+', ['+']],
            ['(', '(', ['(']],
            [')', ')', [')']],
            ['#', '#', ['#']],
            ['$', '$', ['$']],
            ['%', '%', ['%']],
            ['^', '^', ['^']],
            ['&', '&', ['&']],
            ['*', '*', ['*']],
            ['@', '@', ['@']],
            ['/', '/', ['/']],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderSeparator()
    {
        return [
            ['test!test', 'тест тест', '!'],
            ['test]test', '   тест тест   ', ']'],
            ['', '             ', ']'],
            ['', ']', ']'],
            ['test&test&test', 'тест тест тест', '&'],
            ['test/test/test', 'тест тест тест', '/'],
            ['test?test?test', 'тест тест тест', '?'],
        ];
    }
}
