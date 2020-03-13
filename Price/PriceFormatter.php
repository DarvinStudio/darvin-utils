<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Price;

use Darvin\Utils\Service\ServiceProviderInterface;
use Twig\Environment;

/**
 * Price formatter
 */
class PriceFormatter implements PriceFormatterInterface
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $twigProvider;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $twigProvider Twig service provider
     */
    public function __construct(ServiceProviderInterface $twigProvider)
    {
        $this->twigProvider = $twigProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function format($price, array $options = []): string
    {
        if (!isset($options['format'])) {
            $options['format'] = 'default';
        }
        if (!isset($options['empty_format'])) {
            $options['empty_format'] = 'default';
        }
        if (!isset($options['thousands_separator'])) {
            $options['thousands_separator'] = ' ';
        }

        return $this->getTwig()->render(isset($options['template']) ? $options['template'] : '@DarvinUtils/price.html.twig', [
            'price'   => $price,
            'options' => $options,
        ]);
    }

    /**
     * @return \Twig\Environment
     */
    final protected function getTwig(): Environment
    {
        return $this->twigProvider->getService();
    }
}
