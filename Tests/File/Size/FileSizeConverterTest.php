<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\File\Size;

use PHPUnit\Framework\TestCase;

/**
 * File size converter test
 */
class FileSizeConverterTest extends TestCase
{
    /**
     * @dataProvider dataProviderConvert
     *
     * @param mixed                            $size       Size
     * @param string                           $sourceUnit Source unit
     * @param string|null                      $targetUnit Target unit
     * @param \Darvin\Utils\File\Size\FileSize $expected   Expected
     */
    public function testConvert($size, string $sourceUnit, ?string $targetUnit, FileSize $expected): void
    {
        $this->assertEquals($expected, FileSizeConverter::convert($size, $targetUnit, $sourceUnit));
    }

    public function testConvertTargetUnitInvalidException()
    {
        $this->expectException(\InvalidArgumentException::class);

        FileSizeConverter::convert(123, 'fgsdfdsf');
    }

    public function testConvertSourceUnitInvalidException()
    {
        $this->expectException(\InvalidArgumentException::class);

        FileSizeConverter::convert(123, FileSize::UNIT_BYTE, 'dsfsdf');
    }

    /**
     * @return array
     */
    public function dataProviderConvert(): array
    {
        return [
            [2048,                                    FileSize::UNIT_BYTE,     null,                    new FileSize(2.0,               FileSize::UNIT_KILOBYTE)],
            [2048,                                    FileSize::UNIT_KILOBYTE, null,                    new FileSize(2.0,               FileSize::UNIT_MEGABYTE)],
            [123,                                     FileSize::UNIT_KILOBYTE, FileSize::UNIT_KILOBYTE, new FileSize(123.0,             FileSize::UNIT_KILOBYTE)],
            [1,                                       FileSize::UNIT_BYTE,     FileSize::UNIT_KILOBYTE, new FileSize(0.0009765625,      FileSize::UNIT_KILOBYTE)],
            [2,                                       FileSize::UNIT_MEGABYTE, FileSize::UNIT_KILOBYTE, new FileSize(2048.0,            FileSize::UNIT_KILOBYTE)],
            [null,                                    FileSize::UNIT_BYTE,     FileSize::UNIT_BYTE,     new FileSize(0.0,               FileSize::UNIT_BYTE)],
            ['1231239018432509834958349058345809345', FileSize::UNIT_BYTE,     null,                    new FileSize(1018457045466.192, FileSize::UNIT_YOTTABYTE)],
            [-2048,                                   FileSize::UNIT_BYTE,     null,                    new FileSize(-2.0,              FileSize::UNIT_KILOBYTE)],
            [-2,                                      FileSize::UNIT_MEGABYTE, FileSize::UNIT_KILOBYTE, new FileSize(-2048.0,           FileSize::UNIT_KILOBYTE)],
        ];
    }
}
