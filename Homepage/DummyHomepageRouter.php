<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Homepage;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Dummy homepage router
 */
class DummyHomepageRouter implements HomepageRouterInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH, array $params = []): ?string
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

    /**
     * {@inheritDoc}
     */
    public function getHomepageRoute(): ?string
    {
        return null;
    }
}
