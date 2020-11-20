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
 * File size
 */
class FileSize
{
    public const UNIT_BYTE      = 'B';
    public const UNIT_KILOBYTE  = 'KB';
    public const UNIT_MEGABYTE  = 'MB';
    public const UNIT_GIGABYTE  = 'GB';
    public const UNIT_TERABYTE  = 'TB';
    public const UNIT_PETABYTE  = 'PB';
    public const UNIT_EXABYTE   = 'EB';
    public const UNIT_ZETTABYTE = 'ZB';
    public const UNIT_YOTTABYTE = 'YB';

    public const UNITS = [
        self::UNIT_BYTE,
        self::UNIT_KILOBYTE,
        self::UNIT_MEGABYTE,
        self::UNIT_GIGABYTE,
        self::UNIT_TERABYTE,
        self::UNIT_PETABYTE,
        self::UNIT_EXABYTE,
        self::UNIT_ZETTABYTE,
        self::UNIT_YOTTABYTE,
    ];

    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    /**
     * @param float  $value Value
     * @param string $unit  Unit
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(float $value, string $unit)
    {
        if (!in_array($unit, self::UNITS)) {
            throw new \InvalidArgumentException(sprintf('Unit "%s" is not supported.', $unit));
        }

        $this->value = $value;
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%.2f %s', $this->value, $this->unit);
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }
}
