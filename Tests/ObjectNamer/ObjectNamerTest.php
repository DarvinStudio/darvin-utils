<?php
/**
 * @author    Maxim Sukhanov <syhanov.m@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\ObjectNamer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\CssSelector\XPath\Translator;

/**
 * ObjectNamer test
 *
 * @group namer
 */
class ObjectNamerTest extends TestCase
{
    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamer
     */
    private $objectNamer;

    protected function setUp()
    {
        $this->objectNamer = new ObjectNamer();
    }

    /**
     * @dataProvider dataProviderName
     *
     * @param string        $expected
     * @param object|string $input
     */
    public function testName($expected, $input)
    {
        $output = $this->objectNamer->name($input);

        self::assertEquals($expected, $output);
    }

    /**
     * @return array
     */
    public function dataProviderName()
    {
        return [
            [null, null],
            ['', ''],
            ['product_price', 'Product\Product\Price'],
            ['price', 'Product\Entity\Price'],
            ['product_price', 'Product\Product\Product\Price'],
            ['product_price', 'Product\Price'],
            ['product_price', 'Product\PriceInterface'],
            ['product_xprice', 'Product\XPriceInterface'],
            ['product_prod_prod_price', 'Product\ProdProd\PriceInterface'],
            ['price', 'PriceInterface'],
            ['acme_blog_bundle_blog', 'Acme\Bundle\BlogBundle\Blog'],
            ['symfony_component_css_selector_xpath_translator', Translator::class],
        ];
    }
}
