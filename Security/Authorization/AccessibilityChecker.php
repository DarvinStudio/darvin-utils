<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Security\Authorization;

use Darvin\Utils\Security\SecurableInterface;
use Darvin\Utils\Service\ServiceProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Accessibility checker
 */
class AccessibilityChecker implements AccessibilityCheckerInterface
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $authorizationCheckerProvider;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $authorizationCheckerProvider Authorization checker provider
     */
    public function __construct(ServiceProviderInterface $authorizationCheckerProvider)
    {
        $this->authorizationCheckerProvider = $authorizationCheckerProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccessible(SecurableInterface $securable): bool
    {
        $accessible           = true;
        $authorizationChecker = $this->getAuthorizationChecker();

        foreach ($securable->getAllowedRoles() as $allowedRole) {
            $accessible = false;

            if ($authorizationChecker->isGranted($allowedRole)) {
                return true;
            }
        }

        return $accessible;
    }

    /**
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return $this->authorizationCheckerProvider->getService();
    }
}
