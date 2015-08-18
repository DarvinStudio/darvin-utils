<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 18.08.15
 * Time: 16:05
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
