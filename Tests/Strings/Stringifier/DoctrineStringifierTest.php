<?php
/**
 * @author    Maxim Sukhanov <syhanov.m@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings\Stringifier;


use Doctrine\DBAL\Types\Type;
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
     * @var StringifierInterface
     */
    private $stringifier;

    /**
     * {@inheritDoc}
     */
    public function setUp()
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
     * @param string $expected
     * @param mixed  $value
     * @param string $dataType
     */
    public function testStringify($expected, $value, $dataType)
    {
        $output = $this->stringifier->stringify($value, $dataType);
        self::assertEquals($expected,$output);
    }

    public function testStringifyObjectWithToString()
    {
        $obj = $this->getMockBuilder('Stub')
            ->setMethods(['__toString'])
            ->getMock();

        $obj->method('__toString')->willReturn('string object');

        $output = $this->stringifier->stringify($obj, Type::OBJECT);
        self::assertEquals('string object',$output);
    }

    public function testStringifyObject()
    {
        $obj = $this->getMockBuilder('Stub')
            ->setMethods(['doSomething', 'doSomethingElse'])
            ->getMock();

        $obj->arg1 = 1;
        $obj->arg2 = 'string';

        $output = $this->stringifier->stringify($obj, Type::OBJECT);
        self::assertEquals(serialize($obj),$output);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function dataProviderStringify()
    {
        return [
            ['',null, null],
            ['', '', ''],
            ['boolean.1', true, Type::BOOLEAN],
            [1.0E+19, 1.0E+19, Type::BIGINT],
            ['', '12.23.1200', Type::DATE],
            ['01.01.2000', new \DateTime('2000-01-01'), Type::DATE],
            ['', 'array', Type::JSON_ARRAY],
            [json_encode([1, 'two']), [1, 'two'], Type::JSON_ARRAY],
        ];
    }
}
