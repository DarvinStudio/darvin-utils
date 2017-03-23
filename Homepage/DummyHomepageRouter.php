<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
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
     * {@inheritdoc}
     */
    public function generate($referenceType = UrlGeneratorInterface::ABSOLUTE_PATH, array $params = [])
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHomepage($object)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getHomepageRoute()
    {
        return null;
    }
}
