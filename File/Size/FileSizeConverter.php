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
    /**
     * @param int         $bytes      Size in bytes
     * @param string|null $targetUnit Target unit
     * @param int         $base       Base
     *
     * @return \Darvin\Utils\File\Size\FileSize
     */
    public static function convertSize(int $bytes, ?string $targetUnit = null, int $base = 1024): FileSize
    {
        if (null !== $targetUnit) {
            $exponent = array_search($targetUnit, FileSize::UNITS);

            if (false === $exponent) {
                throw new \InvalidArgumentException(sprintf('Unit "%s" is not supported.', $targetUnit));
            }

            return new FileSize($bytes / pow($base, $exponent), $targetUnit);
        }

        $value = (float)$bytes;
        $units = FileSize::UNITS;

        $currentUnit = reset($units);

        while ($value > $base) {
            if (false === next($units)) {
                break;
            }

            $currentUnit = current($units);

            $value /= $base;
        }

        return new FileSize($value, $currentUnit);
    }
}
