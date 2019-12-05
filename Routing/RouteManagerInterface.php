<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
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
    public function exists(string $routeName): bool;

    /**
     * @return string[]
     */
    public function getNames(): array;

    /**
     * @param string $routeName Route name
     * @param string $option    Option
     *
     * @return mixed
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     * @throws \Darvin\Utils\Routing\Exception\RouteHasNoOptionException
     */
    public function getOption(string $routeName, string $option);

    /**
     * @param string $routeName Route name
     * @param string $option    Option
     *
     * @return bool
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public function hasOption(string $routeName, string $option): bool;

    /**
     * @param string $routeName Route name
     *
     * @return string
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     * @throws \Darvin\Utils\Routing\Exception\RouteHasNoPathException
     */
    public function getPath(string $routeName): string;

    /**
     * @param string $routeName   Route name
     * @param string $requirement Requirement
     *
     * @return bool
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public function hasRequirement(string $routeName, string $requirement): bool;

    /**
     * @param string $routeName Route name
     *
     * @return bool
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    public function requiresLocale(string $routeName): bool;
}
