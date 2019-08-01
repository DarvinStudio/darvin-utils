<?php declare(strict_types=1);
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
     * @param mixed|null $text Text
     *
     * @return string|null
     */
    public static function humanize($text): ?string
    {
        if (null === $text) {
            return null;
        }

        $text = (string)$text;

        return ucfirst(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }

    /**
     * @param mixed|null $text Text
     *
     * @return bool
     */
    public static function isUppercase($text): bool
    {
        if (null === $text) {
            return false;
        }

        $text = (string)$text;

        return mb_strtoupper($text) === $text;
    }

    /**
     * @param mixed|null $text Text
     *
     * @return string|null
     */
    public static function lowercaseFirst($text): ?string
    {
        if (null === $text) {
            return null;
        }

        $text = (string)$text;

        return mb_strtolower(mb_substr($text, 0, 1)).mb_substr($text, 1);
    }

    /**
     * @param mixed|null $text Text
     *
     * @return string|null
     */
    public static function toCamelCase($text): ?string
    {
        if (null === $text) {
            return null;
        }

        $text = (string)$text;

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $text)));
    }

    /**
     * @param mixed|null $text Text
     *
     * @return string|null
     */
    public static function toUnderscore($text): ?string
    {
        if (null === $text) {
            return null;
        }

        $text = (string)$text;

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $text));
    }

    /**
     * @param mixed|null $text   Text to truncate
     * @param int        $length Length
     * @param string     $suffix Suffix
     *
     * @return string|null
     */
    public static function truncate($text, int $length, string $suffix = '...'): ?string
    {
        if (null === $text) {
            return null;
        }

        $text = (string)$text;

        if (mb_strlen($text) > $length) {
            return mb_substr(html_entity_decode(strip_tags($text)), 0, $length - mb_strlen($suffix)).$suffix;
        }

        return $text;
    }
}
