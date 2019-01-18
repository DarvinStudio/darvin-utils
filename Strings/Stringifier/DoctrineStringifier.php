<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings\Stringifier;

use Doctrine\DBAL\Types\Type;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Doctrine stringifier
 */
class DoctrineStringifier implements StringifierInterface
{
    private const DATETIME_FORMATS = [
        Type::DATE       => 'd.m.Y',
        Type::DATETIME   => 'd.m.Y H:i:s',
        Type::DATETIMETZ => 'd.m.Y H:i:s',
        Type::TIME       => 'H:i:s',
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
     * {@inheritdoc}
     */
    public function stringify($value, $dataType)
    {
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }
        switch ($dataType) {
            case Type::BIGINT:
            case Type::BLOB:
            case Type::DECIMAL:
            case Type::FLOAT:
            case Type::GUID:
            case Type::INTEGER:
            case Type::SMALLINT:
            case Type::STRING:
            case Type::TEXT:
                return $value;

            case Type::BOOLEAN:
                return $this->stringifyBoolean($value);

            case Type::DATE:
            case Type::DATETIME:
            case Type::DATETIMETZ:
            case Type::TIME:
                return $this->stringifyDatetime($value, self::DATETIME_FORMATS[$dataType]);

            case Type::JSON_ARRAY:
            case Type::SIMPLE_ARRAY:
            case Type::TARRAY:
                return $this->stringifyArray($value);

            case Type::OBJECT:
                return $this->stringifyObject($value);

            default:
                return '';
        }
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function stringifyArray($value)
    {
        return is_array($value) ? json_encode($value) : '';
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function stringifyBoolean($value)
    {
        return $this->translator->trans(sprintf('boolean.%d', $value));
    }

    /**
     * @param mixed  $value  Value to stringify
     * @param string $format Datetime format
     *
     * @return string
     */
    private function stringifyDatetime($value, $format)
    {
        return $value instanceof \DateTime ? $value->format($format) : '';
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function stringifyObject($value)
    {
        return is_object($value) ? serialize($value) : '';
    }
}
