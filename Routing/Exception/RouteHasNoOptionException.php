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
 * Route has no option exception
 */
class RouteHasNoOptionException extends RoutingException
{
    /**
     * @param string $routeName Route name
     * @param string $option    Option
     *
     * @return \Darvin\Utils\Routing\Exception\RouteHasNoOptionException
     */
    public static function create($routeName, $option)
    {
        return new self(sprintf('Route "%s" has no option "%s".', $routeName, $option));
    }
}
