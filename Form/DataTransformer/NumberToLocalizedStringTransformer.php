<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
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
     * {@inheritDoc}
     */
    public function __construct(int $scale = null, ?bool $grouping = false, ?int $roundingMode = self::ROUND_HALF_UP, string $locale = null)
    {
        parent::__construct($scale, $grouping, $roundingMode, $locale);

        $this->scale = $scale;
    }

    /**
     * {@inheritDoc}
     */
    protected function getScale(): ?int
    {
        return $this->scale;
    }
}
