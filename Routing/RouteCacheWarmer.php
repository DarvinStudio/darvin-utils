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

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Route cache warmer
 */
class RouteCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var \Darvin\Utils\Routing\CachedRouteManager
     */
    private $cachedRouteManager;

    /**
     * @param \Darvin\Utils\Routing\CachedRouteManager $cachedRouteManager Cached route manager
     */
    public function __construct(CachedRouteManager $cachedRouteManager)
    {
        $this->cachedRouteManager = $cachedRouteManager;
    }

    /**
     * {@inheritDoc}
     */
    public function isOptional(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function warmUp($cacheDir): void
    {
        $this->cachedRouteManager->cacheRoutes();
    }
}
