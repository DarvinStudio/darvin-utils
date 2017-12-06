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

use Darvin\Utils\Routing\Exception\RouteHasNoOptionException;
use Darvin\Utils\Routing\Exception\RouteNotExistException;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cached route manager
 */
class CachedRouteManager implements RouteManagerInterface
{
    const REQUIREMENT_LOCALE = '_locale';

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $cachePathname;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var array|null
     */
    private $routes;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router        Router
     * @param string                                     $cachePathname Cache pathname
     * @param bool                                       $debug         Whether debug mode is enabled
     */
    public function __construct(RouterInterface $router, $cachePathname, $debug)
    {
        $this->router = $router;
        $this->cachePathname = $cachePathname;
        $this->debug = $debug;

        $this->routes = null;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($routeName)
    {
        $routes = $this->getRoutes();

        return isset($routes[$routeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return array_keys($this->getRoutes());
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($routeName, $option)
    {
        if (!$this->hasOption($routeName, $option)) {
            throw RouteHasNoOptionException::create($routeName, $option);
        }

        return $this->getRoute($routeName)['options'][$option];
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($routeName, $option)
    {
        $route = $this->getRoute($routeName);

        return array_key_exists($option, $route['options']);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($routeName)
    {
        return $this->getRoute($routeName)['path'];
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequirement($routeName, $requirement)
    {
        $requirements = $this->getRoute($routeName)['requirements'];

        return array_key_exists($requirement, $requirements);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresLocale($routeName)
    {
        return $this->hasRequirement($routeName, self::REQUIREMENT_LOCALE);
    }

    public function cacheRoutes()
    {
        $cache = new ConfigCache($this->cachePathname, $this->debug);

        if ($cache->isFresh()) {
            return;
        }

        $routes = [];

        foreach ($this->router->getRouteCollection() as $name => $symfonyRoute) {
            $routes[$name] = [
                'options'      => $symfonyRoute->getOptions(),
                'path'         => $symfonyRoute->getPath(),
                'requirements' => $symfonyRoute->getRequirements(),
            ];
        }

        $cache->write(sprintf('<?php return %s;', var_export($routes, true)));
    }

    /**
     * @param string $routeName Route name
     *
     * @return array
     * @throws \Darvin\Utils\Routing\Exception\RouteNotExistException
     */
    private function getRoute($routeName)
    {
        if (!$this->exists($routeName)) {
            throw RouteNotExistException::create($routeName);
        }

        return $this->getRoutes()[$routeName];
    }

    /**
     * @return array
     */
    private function getRoutes()
    {
        if (null === $this->routes) {
            $this->cacheRoutes();

            $this->routes = require $this->cachePathname;
        }

        return $this->routes;
    }
}
