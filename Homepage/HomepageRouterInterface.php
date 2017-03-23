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
 * Homepage router
 */
interface HomepageRouterInterface
{
    /**
     * @param int   $referenceType Reference type
     * @param array $params        Parameters
     *
     * @return null|string
     */
    public function generate($referenceType = UrlGeneratorInterface::ABSOLUTE_PATH, array $params = []);

    /**
     * @param object $object Object to check
     *
     * @return bool
     */
    public function isHomepage($object);

    /**
     * @return \Symfony\Component\Routing\Route|null
     */
    public function getHomepageRoute();
}
