<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Routing;

/**
 * Route manager
 */
interface RouteManagerInterface
{
    /**
     * @param string $routeName Route name
     *
     * @return bool
     */
    public function exists($routeName);

    /**
     * @param string $routeName Route name
     * @param string $option    Option
     *
     * @return mixed
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     * @throws \Darvin\Utils\Routing\Exception\RouteHasNoOptionException
     */
    public function getOption($routeName, $option);

    /**
     * @param string $routeName Route name
     * @param string $option    Option
     *
     * @return bool
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public function hasOption($routeName, $option);

    /**
     * @param string $routeName Route name
     *
     * @return string
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public function getPath($routeName);

    /**
     * @param string $routeName   Route name
     * @param string $requirement Requirement
     *
     * @return bool
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public function hasRequirement($routeName, $requirement);

    /**
     * @param string $routeName Route name
     *
     * @return bool
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public function requiresLocale($routeName);
}
