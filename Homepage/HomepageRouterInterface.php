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
    public function generate(int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH, array $params = []): ?string;

    /**
     * @param object|null $object Object to check
     *
     * @return bool
     */
    public function isHomepage(?object $object): bool;

    /**
     * @return string|null
     */
    public function getHomepageRoute(): ?string;
}
