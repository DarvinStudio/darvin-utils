<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Security\Authorization;

use Darvin\Utils\Security\SecurableInterface;

/**
 * Accessibility checker
 */
interface AccessibilityCheckerInterface
{
    /**
     * @param \Darvin\Utils\Security\SecurableInterface $securable Securable
     *
     * @return bool
     */
    public function isAccessible(SecurableInterface $securable): bool;
}