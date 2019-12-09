<?php declare(strict_types=1);
/**
 * @author    Maxim Sukhanov <syhanov.m@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings\Stringifier;

use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DoctrineStringifier test
 *
 * @group strings
 */
class DoctrineStringifierTest extends TestCase
{
    /**
     * @var \Darvin\Utils\Strings\Stringifier\DoctrineStringifierInterface
     */
    private $stringifier;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['trans'])
            ->getMock();

        $translator->method('trans')
            ->willReturnArgument(0);

        $this->stringifier = new DoctrineStringifier($translator);
    }

    /**
     * @dataProvider dataProviderStringify
     *
     * @param mixed $expected Expected result
     * @param mixed $value    Value to stringify
     * @param mixed $dataType Doctrine data type
     */
    public function testStringify($expected, $value, $dataType): void
    {
        $output = $this->stringifier->stringify($value, $dataType);
        self::assertEquals($expected,$output);
    }

    public function testStringifyObjectWithToString(): void
    {
        $obj = $this->getMockBuilder('Stub')
            ->setMethods(['__toString'])
            ->getMock();

        $obj->method('__toString')->willReturn('string object');

        $output = $this->stringifier->stringify($obj, Types::OBJECT);
        self::assertEquals('string object',$output);
    }

    public function testStringifyObject(): void
    {
        $obj = $this->getMockBuilder('Stub')
            ->setMethods(['doSomething', 'doSomethingElse'])
            ->getMock();

        $obj->arg1 = 1;
        $obj->arg2 = 'string';

        $output = $this->stringifier->stringify($obj, Types::OBJECT);
        self::assertEquals(serialize($obj),$output);
    }

    /**
     * @return iterable
     */
    public function dataProviderStringify(): iterable
    {
        yield ['', null, ''];
        yield ['', '', ''];
        yield ['boolean.yes', true, Types::BOOLEAN];
        yield [1.0E+19, 1.0E+19, Types::BIGINT];
        yield ['', '12.23.1200', Types::DATE_MUTABLE];
        yield ['01.01.2000', new \DateTime('2000-01-01'), Types::DATE_MUTABLE];
        yield ['', 'array', Types::JSON];
        yield ['[1,"two"]', [1, 'two'], Types::JSON];
    }
}
