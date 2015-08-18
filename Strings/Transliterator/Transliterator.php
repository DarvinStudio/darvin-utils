<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 18.08.15
 * Time: 16:09
 */

namespace Darvin\Utils\Strings\Transliterator;

/**
 * Darvin Studio standard transliterator
 */
class Transliterator implements TransliteratorInterface
{
    /**
     * @var array
     */
    private static $replacePairs = array(
        'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e',  'ж' => 'zh', 'з' => 'z',  'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l',  'м' => 'm',  'н' => 'n',  'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's',  'т' => 't',  'у' => 'u',  'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ы' => 'i', 'ь' => '',  'ъ' => '',
        'э' => 'e',  'ю' => 'yu', 'я' => 'ya',
    );

    /**
     * {@inheritdoc}
     */
    public function transliterate($string, $allowAllNonCyrillic = false, array $allowedSymbols = array('_'), $separator = '-')
    {
        $string = mb_strtolower(preg_replace('/\s+/', ' ', trim($string)));

        $transliterated = strtr($string, self::$replacePairs);

        if ($allowAllNonCyrillic) {
            return $transliterated;
        }

        $transliterated = str_replace(' ', $separator, $transliterated);

        $transliterated = preg_replace(self::createReplacePattern($allowedSymbols, $separator), '', $transliterated);

        return $transliterated;
    }

    /**
     * @param array  $allowedSymbols Allowed symbols
     * @param string $separator      Words separator
     *
     * @return string
     */
    private function createReplacePattern(array $allowedSymbols, $separator)
    {
        $allowedSymbols[] = $separator;
        $allowedSymbols = array_unique($allowedSymbols);

        $pattern = '/[^a-zA-Z0-9';

        $pattern .= implode('', $allowedSymbols);

        $pattern .= ']+/';

        return $pattern;
    }
}
