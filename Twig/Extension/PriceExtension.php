<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Twig\Extension;

use Darvin\Utils\Price\PriceFormatterInterface;

/**
 * Price Twig extension
 */
class PriceExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\Utils\Price\PriceFormatterInterface
     */
    private $priceFormatter;

    /**
     * @param \Darvin\Utils\Price\PriceFormatterInterface $priceFormatter Price formatter
     */
    public function __construct(PriceFormatterInterface $priceFormatter)
    {
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('format_price', [$this->priceFormatter, 'format'], [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
