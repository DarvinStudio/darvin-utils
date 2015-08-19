<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Strings\Transliterator;

/**
 * Transliterator
 */
interface TransliteratorInterface
{
    /**
     * @param string $string         String to transliterate
     * @param bool   $sanitize       Whether to sanitize string
     * @param array  $allowedSymbols Allowed symbols
     * @param string $separator      Words separator
     */
    public function transliterate($string, $sanitize = true, array $allowedSymbols = array('_'), $separator = '-');
}
