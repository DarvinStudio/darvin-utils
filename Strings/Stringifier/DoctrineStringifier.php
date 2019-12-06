<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings\Stringifier;

use Doctrine\DBAL\Types\Types;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Doctrine stringifier
 */
class DoctrineStringifier implements StringifierInterface
{
    private const DATETIME_FORMATS = [
        Types::DATE_MUTABLE       => 'd.m.Y',
        Types::DATETIME_MUTABLE   => 'd.m.Y H:i:s',
        Types::DATETIMETZ_MUTABLE => 'd.m.Y H:i:s',
        Types::TIME_MUTABLE       => 'H:i:s',
    ];

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator Translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function stringify($value, ?string $dataType = null): string
    {
        if (null === $value) {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }
        switch ($dataType) {
            case Types::BIGINT:
            case Types::BLOB:
            case Types::DECIMAL:
            case Types::FLOAT:
            case Types::GUID:
            case Types::INTEGER:
            case Types::SMALLINT:
            case Types::STRING:
            case Types::TEXT:
                return is_scalar($value) ? $this->stringifyScalar($value) : '';

            case Types::BOOLEAN:
                return is_bool($value) ? $this->stringifyBoolean($value) : '';

            case Types::DATE_MUTABLE:
            case Types::DATETIME_MUTABLE:
            case Types::DATETIMETZ_MUTABLE:
            case Types::TIME_MUTABLE:
                return $value instanceof \DateTime ? $this->stringifyDatetime($value, self::DATETIME_FORMATS[$dataType]) : '';

            case Types::JSON:
            case Types::JSON_ARRAY:
            case Types::SIMPLE_ARRAY:
            case Types::ARRAY:
                return is_array($value) ? $this->stringifyArray($value) : '';

            case Types::OBJECT:
                return is_object($value) ? $this->stringifyObject($value) : '';

            default:
                return '';
        }
    }

    /**
     * @param array $value Value to stringify
     *
     * @return string
     */
    private function stringifyArray(array $value): string
    {
        return json_encode($value);
    }

    /**
     * @param bool $value Value to stringify
     *
     * @return string
     */
    private function stringifyBoolean(bool $value): string
    {
        return $this->translator->trans(sprintf('boolean.%s', $value ? 'yes' : 'no'));
    }

    /**
     * @param \DateTime $value  Value to stringify
     * @param string    $format Datetime format
     *
     * @return string
     */
    private function stringifyDatetime(\DateTime $value, string $format): string
    {
        return $value->format($format);
    }

    /**
     * @param object $value Value to stringify
     *
     * @return string
     */
    private function stringifyObject(object $value): string
    {
        return serialize($value);
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function stringifyScalar($value): string
    {
        return (string)$value;
    }
}
