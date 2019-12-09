<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Tests\Strings\Stringifier;

use Darvin\Utils\Strings\Stringifier\Stringifier;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Stringifier test
 *
 * @group strings
 */
class StringifierTest extends TestCase
{
    /**
     * @var \Darvin\Utils\Strings\Stringifier\StringifierInterface
     */
    private $stringifier;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator->method('trans')->willReturnArgument(0);

        $this->stringifier = new Stringifier($translator);
    }

    /**
     * @dataProvider stringifyProvider
     *
     * @param mixed $expected Expected result
     * @param mixed $value    Value to stringify
     */
    public function testStringify($expected, $value): void
    {
        self::assertEquals($expected, $this->stringifier->stringify($value));
    }

    /**
     * @return iterable
     */
    public function stringifyProvider(): iterable
    {
        yield ['boolean.yes', true];
        yield ['boolean.no', false];
        yield ['123', 123];
        yield ['123.123', 123.123];
        yield ['test', 'test'];
        yield ['["foo","bar"]', ['foo', 'bar']];
        yield ['07.07.2007 00:00:00', new \DateTime('2007-07-07')];

        $stub = $this->getMockBuilder('Stub')->setMethods(['__toString'])->getMock();
        $stub->method('__toString')->willReturn('test');

        yield ['test', $stub];

        $stub = $this->getMockBuilder('Stub')->getMock();

        yield [serialize($stub), $stub];
    }
}
