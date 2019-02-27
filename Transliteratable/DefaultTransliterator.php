<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Transliteratable;

/**
 * Default transliterator
 */
class DefaultTransliterator implements TransliteratorInterface
{
    /**
     * @var array
     */
    private static $replacements = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',  'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',  'н' => 'n', 'о' => 'o',  'п' => 'p',  'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h',  'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh',
        'ъ' => '',  'ы' => 'y', 'ь' => '',  'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    /**
     * {@inheritdoc}
     */
    public function transliterate($text, $sanitize = true, array $allowedSymbols = ['_'], $separator = '-')
    {
        $lowercase = mb_strtolower($text);

        $transliterated = strtr($lowercase, self::$replacements);
        $transliterated = \Transliterator::create('Latin-ASCII')->transliterate(\Transliterator::create('Any-Latin')->transliterate($transliterated));
        $transliterated = strtolower($transliterated);

        if (!$sanitize) {
            return $transliterated;
        }

        $sanitized = preg_replace('/\s+/u', $separator, $transliterated);
        $sanitized = trim($sanitized, $separator);
        $sanitized = preg_replace(sprintf('/%s+/', $separator), $separator, $sanitized);
        $sanitized = preg_replace($this->createSanitizePattern($allowedSymbols, $separator), '', $sanitized);

        return $sanitized;
    }

    /**
     * @param string[] $allowedSymbols Allowed symbols
     * @param string   $separator      Word separator
     *
     * @return string
     */
    private function createSanitizePattern(array $allowedSymbols, $separator)
    {
        $allowedSymbols[] = $separator;

        return sprintf('/[^0-9a-zA-Z%s]+/', implode('', array_unique($allowedSymbols)));
    }
}
