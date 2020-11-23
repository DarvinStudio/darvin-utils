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

    /**
     * @return array
     */
    public function dataProviderConvert(): array
    {
        return [
            [null, FileSize::UNIT_BYTE, FileSize::UNIT_BYTE, new FileSize(0.0, FileSize::UNIT_BYTE)],
        ];
    }
}
