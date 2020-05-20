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
 * Stringifier
 */
class Stringifier implements StringifierInterface
{
    use StringifierTrait;

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
     * {@inheritDoc}
     */
    public function stringify($value): string
    {
        return trim($this->doStringify($value));
    }

    /**
     * {@inheritDoc}
     */
    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function doStringify($value): string
    {
        $type = gettype($value);

        switch ($type) {
            case 'boolean':
                return $this->stringifyBoolean($value);

            case 'integer':
            case 'double':
                return $this->stringifyScalar($value);

            case 'string':
                return $value;

            case 'array':
                return $this->stringifyArray($value);

            case 'object':
                return $this->stringifyObject($value);

            default:
                return '';
        }
    }
}
