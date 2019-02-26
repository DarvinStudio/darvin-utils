<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Transliteratable;

/**
 * Transliterator
 */
interface TransliteratorInterface
{
    /**
     * @param mixed    $text           Text to transliterate
     * @param bool     $sanitize       Whether to sanitize text
     * @param string[] $allowedSymbols Allowed symbols
     * @param string   $separator      Word separator
     *
     * @return string
     */
    public function transliterate($text, bool $sanitize = true, array $allowedSymbols = ['_'], string $separator = '-'): string;
}
