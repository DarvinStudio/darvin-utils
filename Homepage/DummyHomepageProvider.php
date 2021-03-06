<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Homepage;

/**
 * Dummy homepage provider
 */
class DummyHomepageProvider implements HomepageProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getHomepage(): ?object
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isHomepage($object): bool
    {
        return false;
    }
}
