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

/**
 * File size converter
 */
class FileSizeConverter
{
    private const BASE = 1024;

    /**
     * @param mixed       $size       Size
     * @param string|null $targetUnit Target unit
     * @param string      $sourceUnit Source unit
     *
     * @return \Darvin\Utils\File\Size\FileSize
     * @throws \InvalidArgumentException
     */
    public static function convert($size, ?string $targetUnit = null, string $sourceUnit = FileSize::UNIT_BYTE): FileSize
    {
        self::validateUnit($targetUnit);
        self::validateUnit($sourceUnit);

        $bytes = (float)$size * pow(self::BASE, array_search($sourceUnit, FileSize::UNITS));

        if (null !== $targetUnit) {
            return new FileSize($bytes / pow(self::BASE, array_search($targetUnit, FileSize::UNITS)), $targetUnit);
        }

        $value = abs($bytes);
        $units = FileSize::UNITS;

        $currentUnit = reset($units);

        while ($value >= self::BASE) {
            if (false === next($units)) {
                break;
            }

            $currentUnit = current($units);

            $value /= self::BASE;
        }
        if ($bytes < 0) {
            $value *= -1;
        }

        return new FileSize($value, $currentUnit);
    }

    /**
     * @param string|null $unit Unit to validate
     *
     * @throws \InvalidArgumentException
     */
    private static function validateUnit(?string $unit): void
    {
        if (null !== $unit && !in_array($unit, FileSize::UNITS)) {
            throw new \InvalidArgumentException(sprintf('Unit "%s" is not supported.', $unit));
        }
    }
}
