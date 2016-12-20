<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Transliteratable;

/**
 * Darvin Studio standard transliterator
 */
class Transliterator implements TransliteratorInterface
{
    /**
     * @var array
     */
    private static $replacePairs = [
        'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e',  'ж' => 'zh', 'з' => 'z',  'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l',  'м' => 'm',  'н' => 'n',  'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's',  'т' => 't',  'у' => 'u',  'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ы' => 'i', 'ь' => '',  'ъ' => '',
        'э' => 'e',  'ю' => 'yu', 'я' => 'ya',
    ];

    /**
     * {@inheritdoc}
     */
    public function transliterate($string, $sanitize = true, array $allowedSymbols = ['_'], $separator = '-')
    {
        $string = mb_strtolower(preg_replace('/\s+/', ' ', trim($string)));

        $transliterated = preg_match('/^[a-zA-Zа-яА-Я0-9\s]+$/u', $string)
            ? strtr($string, self::$replacePairs)
            : \Transliterator::create('Latin-ASCII')->transliterate(\Transliterator::create('Any-Latin')->transliterate($string));

        if (!$sanitize) {
            return $transliterated;
        }

        $transliterated = str_replace(' ', $separator, $transliterated);

        $transliterated = preg_replace($this->createSanitizePattern($allowedSymbols, $separator), '', $transliterated);

        $transliterated = preg_replace(sprintf('/%s+/', $separator), $separator, $transliterated);

        return $transliterated;
    }

    /**
     * @param array  $allowedSymbols Allowed symbols
     * @param string $separator      Words separator
     *
     * @return string
     */
    private function createSanitizePattern(array $allowedSymbols, $separator)
    {
        $allowedSymbols[] = $separator;
        $allowedSymbols = array_unique($allowedSymbols);

        $pattern = '/[^a-zA-Z0-9';

        $pattern .= implode('', $allowedSymbols);

        $pattern .= ']+/';

        return $pattern;
    }
}
