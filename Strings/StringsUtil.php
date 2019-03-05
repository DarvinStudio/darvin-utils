<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings;

/**
 * Strings utility
 */
class StringsUtil
{
    /**
     * @param string|null $text Text
     *
     * @return string|null
     */
    public static function humanize($text)
    {
        if (null === $text) {
            return $text;
        }

        return ucfirst(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }

    /**
     * @param string|null $word Word
     *
     * @return bool
     */
    public static function isUppercase($word)
    {
        if (null === $word) {
            return false;
        }

        return mb_strtoupper($word) === $word;
    }

    /**
     * @param string|null $text Text
     *
     * @return string|null
     */
    public static function lowercaseFirst($text)
    {
        if (null === $text) {
            return $text;
        }

        return mb_strtolower(mb_substr($text, 0, 1)).mb_substr($text, 1);
    }

    /**
     * @param string|null $text Text
     *
     * @return string|null
     */
    public static function toCamelCase($text)
    {
        if (null === $text) {
            return $text;
        }

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $text)));
    }

    /**
     * @param string|null $text Text
     *
     * @return string|null
     */
    public static function toUnderscore($text)
    {
        if (null === $text) {
            return $text;
        }

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $text));
    }
}
