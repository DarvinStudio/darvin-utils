<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Routing\Exception;

/**
 * Route does not exist exception
 */
class RouteNotExistException extends RoutingException
{
    /**
     * @param string $routeName Route name
     *
     * @return \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public static function create($routeName)
    {
        return new self(sprintf('Route "%s" does not exist.', $routeName));
    }
}
