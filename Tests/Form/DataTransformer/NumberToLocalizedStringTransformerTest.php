<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2026, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Form\DataTransformer;

use PHPUnit\Framework\TestCase;

/**
 * Number to localized string form data transformer test
 */
class NumberToLocalizedStringTransformerTest extends TestCase
{
    public function testTransformWithGroupingReturnsGroupedString(): void
    {
        \Locale::setDefault('en');

        $transformer = new NumberToLocalizedStringTransformer(2, true);

        self::assertSame('1,234.50', $transformer->transform(1234.5));
    }
}
