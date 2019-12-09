<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings\Stringifier;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Stringifier trait
 */
trait StringifierTrait
{
    /**
     * @return \Symfony\Contracts\Translation\TranslatorInterface
     */
    abstract protected function getTranslator(): TranslatorInterface;

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
        return $this->getTranslator()->trans(sprintf('boolean.%s', $value ? 'yes' : 'no'));
    }

    /**
     * @param object $value Value to stringify
     *
     * @return string
     */
    private function stringifyObject(object $value): string
    {
        if ($value instanceof \DateTime) {
            return $this->stringifyDatetime($value);
        }
        if (method_exists($value, '__toString')) {
            return (string)$value;
        }

        return serialize($value);
    }

    /**
     * @param \DateTime $value  Value to stringify
     * @param string    $format Datetime format
     *
     * @return string
     */
    private function stringifyDatetime(\DateTime $value, string $format = 'd.m.Y H:i:s'): string
    {
        return $value->format($format);
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
