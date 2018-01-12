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

use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer as BaseNumberToLocalizedStringTransformer;

/**
 * Number to localized string form data transformer
 */
class NumberToLocalizedStringTransformer extends BaseNumberToLocalizedStringTransformer
{
    use NumberToLocalizedStringTransformerTrait;

    /**
     * @var int|null
     */
    private $scale;

    /**
     * {@inheritdoc}
     */
    public function __construct($scale = null, $grouping = false, $roundingMode = self::ROUND_HALF_UP)
    {
        parent::__construct($scale, $grouping, $roundingMode);

        $this->scale = $scale;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScale()
    {
        return $this->scale;
    }
}
