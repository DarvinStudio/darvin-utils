<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Form\DataTransformer;

/**
 * Number to localized string form data transformer trait
 */
trait NumberToLocalizedStringTransformerTrait
{
    /**
     * {@inheritdoc}
     */
    protected function getNumberFormatter()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);

        $scale = $this->getScale();

        if (null !== $scale) {
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $scale);
            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, $this->roundingMode);
        }

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, $this->grouping);

        return $formatter;
    }

    /**
     * @return int|null
     */
    abstract protected function getScale();
}
